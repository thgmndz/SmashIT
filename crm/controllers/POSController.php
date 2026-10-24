<?php
require_once __DIR__ . '/../models/CashRegisterModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';
require_once __DIR__ . '/../models/InvoiceModel.php';

class POSController {
    private CashRegisterModel $posModel;
    private OrderModel $orderModel;
    private ProductModel $productModel;
    private CustomerModel $customerModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->posModel = new CashRegisterModel();
        $this->orderModel = new OrderModel();
        $this->productModel = new ProductModel();
        $this->customerModel = new CustomerModel();
    }

    public function index(): void {
        $error = '';
        $success = '';

        // Procesar acciones de caja
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'open_register') {
                $openingAmount = (float)($_POST['opening_amount'] ?? 0.0);
                
                // Empleado por defecto (Thiago Mendez Admin tiene id 1 en seed)
                $employeeId = 1;
                
                if ($openingAmount >= 0) {
                    if ($this->posModel->openRegister($employeeId, $openingAmount)) {
                        $success = 'Caja abierta correctamente con un monto inicial de ' . number_format($openingAmount, 0, ',', '.') . ' Gs.';
                    } else {
                        $error = 'Error al abrir la caja.';
                    }
                } else {
                    $error = 'Monto de apertura inválido.';
                }
            } elseif ($action === 'close_register') {
                $registerId = (int)($_POST['register_id'] ?? 0);
                $closingAmount = (float)($_POST['closing_amount'] ?? 0.0);
                $calculatedAmount = (float)($_POST['calculated_amount'] ?? 0.0);

                if ($registerId > 0 && $closingAmount >= 0) {
                    if ($this->posModel->closeRegister($registerId, $closingAmount, $calculatedAmount)) {
                        $success = 'Caja cerrada correctamente. Diferencia registrada.';
                    } else {
                        $error = 'Error al cerrar la caja.';
                    }
                } else {
                    $error = 'Parámetros de cierre incorrectos.';
                }
            } elseif ($action === 'pay_order') {
                $orderId = (int)($_POST['order_id'] ?? 0);
                $registerId = (int)($_POST['register_id'] ?? 0);
                $paymentMethod = trim($_POST['payment_method'] ?? 'Efectivo');
                $invoiceType = trim($_POST['invoice_type'] ?? 'ticket');
                $itemsJson = $_POST['items_json'] ?? '';

                if ($orderId > 0 && $registerId > 0) {
                    try {
                        $db = CRMDatabase::getConnection();
                        $db->beginTransaction();

                        // Obtener código de pedido y cliente
                        $stmt = $db->prepare("SELECT order_code FROM orders WHERE id = :id");
                        $stmt->execute([':id' => $orderId]);
                        $orderCode = $stmt->fetchColumn();

                        // Recibir datos de la factura
                        $billingMode = trim($_POST['billing_mode'] ?? 'pedido');
                        $invoiceDate = trim($_POST['invoice_date'] ?? date('Y-m-d'));
                        $timbradoNo = trim($_POST['timbrado_no'] ?? '12345678');
                        $timbradoExpiry = trim($_POST['timbrado_expiry'] ?? date('Y-m-d'));
                        $customerId = (int)($_POST['customer_select_id'] ?? 0);
                        $sucursal = trim($_POST['sucursal'] ?? 'Casa Central');
                        $notes = trim($_POST['notes'] ?? '');

                        // Procesar ítems si se enviaron actualizaciones
                        $newItems = json_decode($itemsJson, true);
                        if (!empty($newItems) && is_array($newItems)) {
                            // Borrar ítems existentes
                            $stmtDel = $db->prepare("DELETE FROM order_items WHERE order_id = :id");
                            $stmtDel->execute([':id' => $orderId]);

                            // Insertar ítems actualizados
                            $stmtItem = $db->prepare("
                                INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal, options_summary) 
                                VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :subtotal, '')
                            ");
                            
                            $newSubtotal = 0.0;
                            foreach ($newItems as $item) {
                                $itemSubtotal = (float)$item['unit_price'] * (int)$item['quantity'];
                                $newSubtotal += $itemSubtotal;
                                $stmtItem->execute([
                                    ':order_id' => $orderId,
                                    ':product_id' => (int)$item['product_id'],
                                    ':product_name' => trim($item['product_name']),
                                    ':unit_price' => (float)$item['unit_price'],
                                    ':quantity' => (int)$item['quantity'],
                                    ':subtotal' => $itemSubtotal
                                ]);
                            }

                            // Actualizar montos en cabecera
                            $stmtOrderUpd = $db->prepare("UPDATE orders SET subtotal = :subtotal, total_amount = :total_amount WHERE id = :id");
                            $stmtOrderUpd->execute([
                                ':id' => $orderId,
                                ':subtotal' => $newSubtotal,
                                ':total_amount' => $newSubtotal
                            ]);
                        }

                        // Procesar pago en caja
                        if ($this->posModel->processOrderPayment($orderId, $registerId, $paymentMethod)) {
                            // Si se especificó un cliente para facturar, emitimos la factura automáticamente
                            if ($customerId > 0) {
                                // Obtener siguiente número de factura
                                $stmtMax = $db->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) FROM invoices");
                                $maxSeq = (int)$stmtMax->fetchColumn();
                                $nextSeq = $maxSeq + 1;
                                $invoiceNumber = "001-001-" . str_pad($nextSeq, 7, '0', STR_PAD_LEFT);

                                // Obtener los ítems finales facturados
                                $stmtFinalItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
                                $stmtFinalItems->execute([':order_id' => $orderId]);
                                $finalItems = $stmtFinalItems->fetchAll();

                                $totalExento = 0.0;
                                $totalIva5 = 0.0;
                                $totalIva10 = 0.0;
                                $totalAmount = 0.0;

                                foreach ($finalItems as $item) {
                                    $sub = (float)$item['subtotal'];
                                    $totalAmount += $sub;
                                    // Por defecto, comida de restaurante tiene IVA 10%
                                    $totalIva10 += round($sub / 11);
                                }

                                // Insertar factura cabecera
                                $stmtInv = $db->prepare("
                                    INSERT INTO invoices (order_id, invoice_number, billing_mode, invoice_date, `condition`, timbrado_no, timbrado_expiry, customer_id, sucursal, notes, total_exento, total_iva5, total_iva10, total_amount, status, created_at)
                                    VALUES (:order_id, :invoice_number, :billing_mode, :invoice_date, 'CONTADO', :timbrado_no, :timbrado_expiry, :customer_id, :sucursal, :notes, :total_exento, :total_iva5, :total_iva10, :total_amount, 'emitida', NOW())
                                ");
                                $stmtInv->execute([
                                    ':order_id' => $orderId,
                                    ':invoice_number' => $invoiceNumber,
                                    ':billing_mode' => $billingMode,
                                    ':invoice_date' => $invoiceDate,
                                    ':timbrado_no' => $timbradoNo,
                                    ':timbrado_expiry' => $timbradoExpiry,
                                    ':customer_id' => $customerId,
                                    ':sucursal' => $sucursal,
                                    ':notes' => $notes,
                                    ':total_exento' => $totalExento,
                                    ':total_iva5' => $totalIva5,
                                    ':total_iva10' => $totalIva10,
                                    ':total_amount' => $totalAmount
                                ]);

                                $invoiceId = (int)$db->lastInsertId();

                                // Insertar factura detalles
                                $stmtInvItem = $db->prepare("
                                    INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, unit_price, subtotal, tax_rate, tax_amount)
                                    VALUES (:invoice_id, :product_id, :product_name, :quantity, :unit_price, :subtotal, :tax_rate, :tax_amount)
                                ");
                                foreach ($finalItems as $item) {
                                    $sub = (float)$item['subtotal'];
                                    $stmtInvItem->execute([
                                        ':invoice_id' => $invoiceId,
                                        ':product_id' => (int)$item['product_id'],
                                        ':product_name' => $item['product_name'],
                                        ':quantity' => (int)$item['quantity'],
                                        ':unit_price' => (float)$item['unit_price'],
                                        ':subtotal' => $sub,
                                        ':tax_rate' => 10,
                                        ':tax_amount' => round($sub / 11)
                                    ]);
                                }
                            }

                            // Liberar mesa si aplica
                            $stmtTable = $db->prepare("UPDATE restaurant_tables SET status = 'libre', current_order_code = NULL WHERE current_order_code = :order_code");
                            $stmtTable->execute([':order_code' => $orderCode]);

                            $db->commit();
                            $success = 'Pago del pedido procesado y factura emitida con éxito. Estado cambiado a COMPLETADO.';
                            $_SESSION['print_order_url'] = "print.php?id={$orderId}&type={$invoiceType}";
                        } else {
                            throw new Exception("Error al marcar pedido como cobrado.");
                        }
                    } catch (Exception $e) {
                        if (isset($db)) $db->rollBack();
                        $error = 'Error al procesar el cobro o emitir la factura: ' . $e->getMessage();
                    }
                } else {
                    $error = 'La caja debe estar abierta para cobrar pedidos.';
                }
            } elseif ($action === 'pay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                $registerId = (int)($_POST['register_id'] ?? 0);
                $paymentMethod = trim($_POST['payment_method'] ?? 'Efectivo');
                $invoiceType = trim($_POST['invoice_type'] ?? 'autoimpresa');

                if ($invoiceId > 0 && $registerId > 0) {
                    try {
                        $invoiceModel = new InvoiceModel();
                        if ($invoiceModel->payInvoice($invoiceId, $registerId, $paymentMethod)) {
                            $success = 'Cobro de la factura procesado con éxito. Estado cambiado a EMITIDA.';
                            $_SESSION['print_order_url'] = "print_invoice.php?id={$invoiceId}&type={$invoiceType}";
                        } else {
                            throw new Exception("Error al marcar factura como cobrada.");
                        }
                    } catch (Exception $e) {
                        $error = 'Error al procesar el cobro: ' . $e->getMessage();
                    }
                } else {
                    $error = 'La caja debe estar abierta para cobrar facturas.';
                }
            }
        }

        // Obtener datos para la vista
        $openRegister = $this->posModel->getOpenRegister();
        $unpaidOrders = $this->posModel->getUnpaidOrders();
        $unpaidInvoices = $this->posModel->getUnpaidInvoices();
        $products = $this->productModel->getAll();
        $customers = $this->customerModel->getAll();
        
        $shiftSales = 0.0;
        $shiftOrdersCount = 0;
        $shiftOrders = [];
        $calculatedAmount = 0.0;

        if ($openRegister) {
            $shiftSales = $this->posModel->getSalesInRegister($openRegister['id']);
            $shiftOrders = $this->posModel->getOrdersInRegister($openRegister['id']);
            $shiftOrdersCount = count($shiftOrders);
            $calculatedAmount = (float)$openRegister['opening_amount'] + $shiftSales;
        }

        require_once __DIR__ . '/../views/pos/index.php';
    }
}
