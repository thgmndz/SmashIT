<?php
require_once __DIR__ . '/../models/TableModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';

class TableController {
    private TableModel $tableModel;
    private ProductModel $productModel;
    private OrderModel $orderModel;
    private CustomerModel $customerModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->tableModel = new TableModel();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
    }

    public function index(): void {
        $tables = $this->tableModel->getAll();
        $products = $this->productModel->getAll(); // Cargar productos para el modal
        $customers = $this->customerModel->getAll(); // Cargar clientes para el modal
        
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $tableNumber = (int)($_POST['table_number'] ?? 0);
                $capacity = (int)($_POST['capacity'] ?? 4);

                if ($tableNumber > 0 && $capacity > 0) {
                    try {
                        $result = $this->tableModel->create($tableNumber, $capacity);
                        if ($result) {
                            $success = 'Mesa registrada correctamente.';
                            $tables = $this->tableModel->getAll();
                        } else {
                            $error = 'Error al registrar la mesa.';
                        }
                    } catch (Exception $e) {
                        $error = 'La mesa número ' . $tableNumber . ' ya está registrada.';
                    }
                } else {
                    $error = 'Valores de mesa o capacidad incorrectos.';
                }
            } elseif ($action === 'update_status') {
                $id = (int)($_POST['id'] ?? 0);
                $status = trim($_POST['status'] ?? 'libre');
                $orderCode = trim($_POST['current_order_code'] ?? '');
                
                if (empty($orderCode)) {
                    $orderCode = null;
                }

                $resName = null;
                $resCi = null;
                $resDatetime = null;

                if ($status === 'reservado') {
                    $resName = trim($_POST['reservation_name'] ?? '');
                    $resCi = trim($_POST['reservation_ci'] ?? '');
                    $resDate = trim($_POST['reservation_date'] ?? '');
                    $resTime = trim($_POST['reservation_time'] ?? '');
                    if (!empty($resDate) && !empty($resTime)) {
                        $resDatetime = $resDate . ' ' . $resTime . ':00';
                    }
                }

                $validStatuses = ['libre', 'ocupado', 'reservado'];
                if ($id > 0 && in_array($status, $validStatuses)) {
                    $result = $this->tableModel->updateStatus($id, $status, $orderCode, $resName, $resCi, $resDatetime);
                    if ($result) {
                        $success = 'Estado de mesa actualizado correctamente.';
                        $tables = $this->tableModel->getAll();
                    } else {
                        $error = 'Error al actualizar el estado de la mesa.';
                    }
                } else {
                    $error = 'Parámetros inválidos.';
                }
            } elseif ($action === 'take_order') {
                $tableId = (int)($_POST['table_id'] ?? 0);
                $tableNumber = (int)($_POST['table_number'] ?? 0);
                $customerId = $_POST['customer_id'] ?? 'new';
                $customerName = trim($_POST['customer_name'] ?? '');
                $customerPhone = trim($_POST['customer_phone'] ?? '');
                $ruc = trim($_POST['ruc'] ?? '');
                $paymentMethod = trim($_POST['payment_method'] ?? 'Efectivo');
                $quantities = $_POST['quantities'] ?? [];

                // Validaciones iniciales
                if ($tableId <= 0 || empty($customerName)) {
                    $error = 'Falta el nombre del cliente o datos de la mesa.';
                } else {
                    // Si el cliente se agrega/edita manualmente (customer_id === 'new') o si no tiene id válido,
                    // intentamos registrarlo si su teléfono no está en la base de datos.
                    if ($customerId === 'new' && !empty($customerPhone)) {
                        $existingCust = $this->customerModel->getByPhone($customerPhone);
                        if (!$existingCust) {
                            $this->customerModel->create([
                                'name' => $customerName,
                                'phone' => $customerPhone,
                                'ruc_ci' => $ruc,
                                'email' => '',
                                'address' => '',
                                'total_orders' => 0
                            ]);
                        }
                    }

                    // Filtrar productos seleccionados con cantidad mayor a 0
                    $selectedItems = [];
                    $subtotal = 0.0;
                    
                    foreach ($quantities as $prodId => $qty) {
                        $qty = (int)$qty;
                        if ($qty > 0) {
                            $prod = $this->productModel->getById((int)$prodId);
                            if ($prod) {
                                $itemSubtotal = (float)$prod['base_price'] * $qty;
                                $subtotal += $itemSubtotal;
                                $selectedItems[] = [
                                    'product_id' => $prod['id'],
                                    'product_name' => $prod['name'],
                                    'unit_price' => (float)$prod['base_price'],
                                    'quantity' => $qty,
                                    'subtotal' => $itemSubtotal
                                ];
                            }
                        }
                    }

                    if (empty($selectedItems)) {
                        $error = 'Debe seleccionar al menos un producto con cantidad mayor a 0.';
                    } else {
                        try {
                            $db = CRMDatabase::getConnection();
                            $db->beginTransaction();

                            // Generar código único de pedido
                            $uniqueId = strtoupper(substr(md5(uniqid()), 0, 4));
                            $orderCode = "SMASH-M" . $tableNumber . "-" . $uniqueId;

                            // Nota conteniendo el número de mesa y RUC si existe
                            $notes = "Mesa Nro: " . $tableNumber;
                            if (!empty($ruc)) {
                                $notes .= " | RUC: " . $ruc;
                            }

                            // Insertar pedido en orders
                            $stmtOrder = $db->prepare("
                                INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, delivery_type, payment_method, subtotal, delivery_fee, total_amount, status, notes, created_at, updated_at) 
                                VALUES (:order_code, :customer_name, 'mesa@smashit.com', :customer_phone, 'dinein', :payment_method, :subtotal, 0.00, :total_amount, 'pending', :notes, NOW(), NOW())
                            ");
                            $stmtOrder->execute([
                                ':order_code' => $orderCode,
                                ':customer_name' => $customerName,
                                ':customer_phone' => !empty($customerPhone) ? $customerPhone : 'N/A',
                                ':payment_method' => $paymentMethod,
                                ':subtotal' => $subtotal,
                                ':total_amount' => $subtotal,
                                ':notes' => $notes
                            ]);

                            $orderId = $db->lastInsertId();

                            // Insertar ítems en order_items
                            $stmtItem = $db->prepare("
                                INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal, options_summary) 
                                VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :subtotal, '')
                            ");

                            foreach ($selectedItems as $item) {
                                $stmtItem->execute([
                                    ':order_id' => $orderId,
                                    ':product_id' => $item['product_id'],
                                    ':product_name' => $item['product_name'],
                                    ':unit_price' => $item['unit_price'],
                                    ':quantity' => $item['quantity'],
                                    ':subtotal' => $item['subtotal']
                                ]);
                            }

                            // Asociar comanda activa a la mesa
                            $this->tableModel->updateStatus($tableId, 'ocupado', $orderCode);

                            $db->commit();
                            $success = 'Pedido tomado correctamente para la Mesa ' . $tableNumber . '. Código: ' . $orderCode;
                            $tables = $this->tableModel->getAll();
                            $customers = $this->customerModel->getAll(); // Recargar clientes
                        } catch (Exception $e) {
                            if (isset($db)) $db->rollBack();
                            $error = 'Error al registrar el pedido: ' . $e->getMessage();
                        }
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->tableModel->delete($id)) {
                    $success = 'Mesa eliminada correctamente.';
                    $tables = $this->tableModel->getAll();
                } else {
                    $error = 'Error al eliminar la mesa.';
                }
            }
        }

        require_once __DIR__ . '/../views/tables/index.php';
    }
}
