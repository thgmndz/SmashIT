<?php
require_once __DIR__ . '/../models/InvoiceModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';

require_once __DIR__ . '/../models/OrderModel.php';

class InvoiceController {
    private InvoiceModel $invoiceModel;
    private ProductModel $productModel;
    private CustomerModel $customerModel;
    private OrderModel $orderModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->invoiceModel = new InvoiceModel();
        $this->productModel = new ProductModel();
        $this->customerModel = new CustomerModel();
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $customerId = (int)($_POST['customer_id'] ?? 0);
                $orderId = (int)($_POST['order_id'] ?? 0);
                $billingMode = trim($_POST['billing_mode'] ?? 'manual');
                $invoiceDate = trim($_POST['invoice_date'] ?? date('Y-m-d'));
                $timbradoNo = trim($_POST['timbrado_no'] ?? '12345678');
                $timbradoExpiry = trim($_POST['timbrado_expiry'] ?? date('Y-m-d'));
                $sucursal = trim($_POST['sucursal'] ?? 'Casa Central');
                $notes = trim($_POST['notes'] ?? '');
                $itemsJson = $_POST['items_json'] ?? '';

                $items = json_decode($itemsJson, true);

                if ($customerId <= 0) {
                    $error = 'Debe seleccionar un cliente válido.';
                } elseif (empty($items)) {
                    $error = 'Debe agregar al menos un ítem a la factura.';
                } else {
                    try {
                        // Calcular totales
                        $totalExento = 0.0;
                        $totalIva5 = 0.0;
                        $totalIva10 = 0.0;
                        $totalAmount = 0.0;

                        foreach ($items as $item) {
                            $qty = (int)$item['quantity'];
                            $price = (float)$item['unit_price'];
                            $subtotal = $qty * $price;
                            $totalAmount += $subtotal;
                            $taxRate = (int)($item['tax_rate'] ?? 10);

                            if ($taxRate === 10) {
                                $totalIva10 += round($subtotal / 11);
                            } elseif ($taxRate === 5) {
                                $totalIva5 += round($subtotal / 21);
                            } else {
                                $totalExento += $subtotal;
                            }
                        }

                        $data = [
                            'order_id' => $orderId > 0 ? $orderId : null,
                            'billing_mode' => $billingMode,
                            'invoice_date' => $invoiceDate,
                            'condition' => 'CONTADO',
                            'timbrado_no' => $timbradoNo,
                            'timbrado_expiry' => $timbradoExpiry,
                            'customer_id' => $customerId,
                            'sucursal' => $sucursal,
                            'notes' => $notes,
                            'total_exento' => $totalExento,
                            'total_iva5' => $totalIva5,
                            'total_iva10' => $totalIva10,
                            'total_amount' => $totalAmount,
                            'status' => 'pendiente'
                        ];

                        $this->invoiceModel->create($data, $items);
                        $success = 'Facturacion guardada correctamente. Pase al módulo de caja para realizar el cobro.';
                    } catch (Exception $e) {
                        $error = 'Error al emitir factura: ' . $e->getMessage();
                    }
                }
            } elseif ($action === 'cancel') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    if ($this->invoiceModel->cancel($id)) {
                        $success = 'Factura anulada correctamente.';
                    } else {
                        $error = 'Error al anular la factura.';
                    }
                }
            }
        }

        // Cargar listas
        $invoices = $this->invoiceModel->getAll();
        $products = $this->productModel->getAll();
        $customers = $this->customerModel->getAll();
        
        $orders = $this->orderModel->getAll();
        $ordersData = [];
        foreach ($orders as $order) {
            if ($order['status'] === 'completed') {
                continue;
            }
            $fullOrder = $this->orderModel->getById($order['id']);
            $ordersData[] = $fullOrder;
        }

        require_once __DIR__ . '/../views/invoices/index.php';
    }
}
