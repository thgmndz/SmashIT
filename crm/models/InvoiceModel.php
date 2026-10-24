<?php
class InvoiceModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT i.*, c.name AS customer_name 
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            ORDER BY i.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT i.*, c.name AS customer_name, c.phone AS customer_phone, c.ruc_ci AS customer_ruc
            FROM invoices i 
            JOIN customers c ON i.customer_id = c.id 
            WHERE i.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $invoice = $stmt->fetch();
        return $invoice ? $invoice : null;
    }

    public function getItems(int $invoiceId): array {
        $stmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :invoice_id");
        $stmt->execute([':invoice_id' => $invoiceId]);
        return $stmt->fetchAll();
    }

    public function getNextInvoiceNumber(): string {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED)) FROM invoices");
        $maxSeq = (int)$stmt->fetchColumn();
        $nextSeq = $maxSeq + 1;
        return "001-001-" . str_pad($nextSeq, 7, '0', STR_PAD_LEFT);
    }

    public function create(array $data, array $items): int {
        try {
            $this->db->beginTransaction();

            $invoiceNumber = $this->getNextInvoiceNumber();

            // Insertar cabecera de la factura
            $stmt = $this->db->prepare("
                INSERT INTO invoices (order_id, cash_register_id, invoice_number, billing_mode, invoice_date, `condition`, timbrado_no, timbrado_expiry, customer_id, sucursal, notes, total_exento, total_iva5, total_iva10, total_amount, status, created_at)
                VALUES (:order_id, :cash_register_id, :invoice_number, :billing_mode, :invoice_date, :condition, :timbrado_no, :timbrado_expiry, :customer_id, :sucursal, :notes, :total_exento, :total_iva5, :total_iva10, :total_amount, :status, NOW())
            ");
            
            $stmt->execute([
                ':order_id' => $data['order_id'] ?? null,
                ':cash_register_id' => $data['cash_register_id'] ?? null,
                ':invoice_number' => $invoiceNumber,
                ':billing_mode' => $data['billing_mode'] ?? 'manual',
                ':invoice_date' => $data['invoice_date'] ?? date('Y-m-d'),
                ':condition' => $data['condition'] ?? 'CONTADO',
                ':timbrado_no' => $data['timbrado_no'] ?? '12345678',
                ':timbrado_expiry' => $data['timbrado_expiry'] ?? date('Y-m-d'),
                ':customer_id' => (int)$data['customer_id'],
                ':sucursal' => $data['sucursal'] ?? 'Casa Central',
                ':notes' => $data['notes'] ?? '',
                ':total_exento' => (float)($data['total_exento'] ?? 0.00),
                ':total_iva5' => (float)($data['total_iva5'] ?? 0.00),
                ':total_iva10' => (float)($data['total_iva10'] ?? 0.00),
                ':total_amount' => (float)$data['total_amount'],
                ':status' => $data['status'] ?? 'pendiente'
            ]);

            $invoiceId = (int)$this->db->lastInsertId();

            // Insertar detalles de la factura
            $stmtItem = $this->db->prepare("
                INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, unit_price, subtotal, tax_rate, tax_amount)
                VALUES (:invoice_id, :product_id, :product_name, :quantity, :unit_price, :subtotal, :tax_rate, :tax_amount)
            ");

            foreach ($items as $item) {
                $qty = (int)$item['quantity'];
                $price = (float)$item['unit_price'];
                $subtotal = $qty * $price;
                $taxRate = (int)($item['tax_rate'] ?? 10);
                
                // Calcular IVA de acuerdo a la tasa
                $taxAmount = 0.0;
                if ($taxRate === 10) {
                    $taxAmount = round($subtotal / 11);
                } elseif ($taxRate === 5) {
                    $taxAmount = round($subtotal / 21);
                }

                $stmtItem->execute([
                    ':invoice_id' => $invoiceId,
                    ':product_id' => (int)$item['product_id'],
                    ':product_name' => trim($item['product_name']),
                    ':quantity' => $qty,
                    ':unit_price' => $price,
                    ':subtotal' => $subtotal,
                    ':tax_rate' => $taxRate,
                    ':tax_amount' => $taxAmount
                ]);
            }

            $this->db->commit();
            return $invoiceId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function cancel(int $id): bool {
        $stmt = $this->db->prepare("UPDATE invoices SET status = 'anulada' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function payInvoice(int $invoiceId, int $registerId, string $paymentMethod): bool {
        try {
            $this->db->beginTransaction();

            // 1. Update the invoice status and link it to the cash register
            $stmt = $this->db->prepare("UPDATE invoices SET status = 'emitida', cash_register_id = :register_id WHERE id = :id");
            $stmt->execute([
                ':id' => $invoiceId,
                ':register_id' => $registerId
            ]);

            // 2. Fetch the invoice to check if it has a linked order_id
            $stmtInv = $this->db->prepare("SELECT order_id, total_amount FROM invoices WHERE id = :id");
            $stmtInv->execute([':id' => $invoiceId]);
            $inv = $stmtInv->fetch();

            if ($inv && $inv['order_id']) {
                $orderId = (int)$inv['order_id'];
                
                // Get order code
                $stmtOrder = $this->db->prepare("SELECT order_code FROM orders WHERE id = :order_id");
                $stmtOrder->execute([':order_id' => $orderId]);
                $orderCode = $stmtOrder->fetchColumn();

                // 3. Mark the order as completed and link to register
                $stmtUpdOrder = $this->db->prepare("UPDATE orders SET status = 'completed', cash_register_id = :register_id, payment_method = :payment_method, updated_at = NOW() WHERE id = :order_id");
                $stmtUpdOrder->execute([
                    ':order_id' => $orderId,
                    ':register_id' => $registerId,
                    ':payment_method' => $paymentMethod
                ]);

                // 4. Free the table if applicable
                if ($orderCode) {
                    $stmtTable = $this->db->prepare("UPDATE restaurant_tables SET status = 'libre', current_order_code = NULL WHERE current_order_code = :order_code");
                    $stmtTable->execute([':order_code' => $orderCode]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
