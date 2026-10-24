<?php
class CashRegisterModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getOpenRegister(): ?array {
        $stmt = $this->db->query("SELECT * FROM cash_registers WHERE status = 'open' LIMIT 1");
        $register = $stmt->fetch();
        return $register ? $register : null;
    }

    public function openRegister(int $employeeId, float $openingAmount): bool {
        $stmt = $this->db->prepare("INSERT INTO cash_registers (employee_id, opening_amount, status, opened_at) VALUES (:employee_id, :opening_amount, 'open', NOW())");
        return $stmt->execute([
            ':employee_id' => $employeeId,
            ':opening_amount' => $openingAmount
        ]);
    }

    public function closeRegister(int $id, float $closingAmount, float $calculatedAmount): bool {
        $difference = $closingAmount - $calculatedAmount;
        $stmt = $this->db->prepare("UPDATE cash_registers SET closing_amount = :closing_amount, calculated_amount = :calculated_amount, difference = :difference, status = 'closed', closed_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':closing_amount' => $closingAmount,
            ':calculated_amount' => $calculatedAmount,
            ':difference' => $difference
        ]);
    }

    public function getSalesInRegister(int $registerId): float {
        $stmtOrders = $this->db->prepare("SELECT SUM(total_amount) FROM orders WHERE cash_register_id = :register_id AND status = 'completed'");
        $stmtOrders->execute([':register_id' => $registerId]);
        $ordersTotal = (float)$stmtOrders->fetchColumn();

        $stmtInvoices = $this->db->prepare("SELECT SUM(total_amount) FROM invoices WHERE cash_register_id = :register_id AND status = 'emitida' AND order_id IS NULL");
        $stmtInvoices->execute([':register_id' => $registerId]);
        $invoicesTotal = (float)$stmtInvoices->fetchColumn();

        return $ordersTotal + $invoicesTotal;
    }

    public function getOrdersInRegister(int $registerId): array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE cash_register_id = :register_id AND status = 'completed' ORDER BY updated_at DESC");
        $stmt->execute([':register_id' => $registerId]);
        return $stmt->fetchAll();
    }

    public function processOrderPayment(int $orderId, int $registerId, string $paymentMethod): bool {
        $stmt = $this->db->prepare("UPDATE orders SET cash_register_id = :register_id, status = 'completed', payment_method = :payment_method, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':id' => $orderId,
            ':register_id' => $registerId,
            ':payment_method' => $paymentMethod
        ]);
    }

    public function getUnpaidOrders(): array {
        $stmt = $this->db->query("
            SELECT DISTINCT o.* 
            FROM orders o
            JOIN invoices i ON o.id = i.order_id
            WHERE o.status = 'ready' AND i.status = 'emitida'
            ORDER BY o.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    public function getUnpaidInvoices(): array {
        $stmt = $this->db->query("
            SELECT i.*, COALESCE(c.name, 'Cliente General') AS customer_name, c.phone AS customer_phone, c.ruc_ci AS customer_ruc
            FROM invoices i
            LEFT JOIN customers c ON i.customer_id = c.id
            WHERE i.status = 'pendiente'
            ORDER BY i.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT cr.*, e.name AS employee_name 
            FROM cash_registers cr
            LEFT JOIN employees e ON cr.employee_id = e.id
            ORDER BY cr.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT cr.*, e.name AS employee_name FROM cash_registers cr LEFT JOIN employees e ON cr.employee_id = e.id WHERE cr.id = :id");
        $stmt->execute([':id' => $id]);
        $register = $stmt->fetch();
        return $register ? $register : null;
    }

    public function updateRegister(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE cash_registers 
            SET employee_id = :employee_id, 
                opening_amount = :opening_amount, 
                closing_amount = :closing_amount, 
                calculated_amount = :calculated_amount, 
                difference = :difference, 
                status = :status,
                opened_at = :opened_at,
                closed_at = :closed_at
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':employee_id' => (int)$data['employee_id'],
            ':opening_amount' => (float)$data['opening_amount'],
            ':closing_amount' => $data['closing_amount'] !== '' ? (float)$data['closing_amount'] : null,
            ':calculated_amount' => $data['calculated_amount'] !== '' ? (float)$data['calculated_amount'] : null,
            ':difference' => $data['difference'] !== '' ? (float)$data['difference'] : null,
            ':status' => $data['status'],
            ':opened_at' => $data['opened_at'],
            ':closed_at' => $data['closed_at'] ?: null
        ]);
    }

    public function deleteRegister(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM cash_registers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

