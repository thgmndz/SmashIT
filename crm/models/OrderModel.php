<?php
class OrderModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch();
        if ($order) {
            // Obtener también los ítems del pedido
            $itemsStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
            $itemsStmt->execute([':order_id' => $id]);
            $order['items'] = $itemsStmt->fetchAll();
        }
        return $order ? $order : null;
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->db->prepare("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status
        ]);
    }

    public function getTotalCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }

    public function getTotalSales(): float {
        return (float)$this->db->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn();
    }

    public function getRecentOrders(int $limit = 5): array {
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSalesByStatus(): array {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count, SUM(total_amount) as total FROM orders GROUP BY status");
        return $stmt->fetchAll();
    }

    public function getCompletedOrders(): array {
        $stmt = $this->db->query("SELECT * FROM orders WHERE status = 'completed' ORDER BY updated_at DESC");
        return $stmt->fetchAll();
    }

    public function getTopProductsThisMonth(int $limit = 10): array {
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');

        $stmt = $this->db->prepare("
            SELECT oi.product_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed' AND o.created_at BETWEEN :start_date AND :end_date
            GROUP BY oi.product_name
            ORDER BY total_qty DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDailySalesThisMonth(): array {
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');

        $stmt = $this->db->prepare("
            SELECT SUBSTR(created_at, 1, 10) as sale_date, COUNT(*) as qty, SUM(total_amount) as total_amount
            FROM orders
            WHERE status = 'completed' AND created_at BETWEEN :start_date AND :end_date
            GROUP BY sale_date
            ORDER BY sale_date ASC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll();
    }

    public function getPaymentReport(): array {
        $stmt = $this->db->query("
            SELECT 
                SUBSTR(created_at, 1, 7) as month_period, 
                payment_method, 
                COUNT(*) as total_orders, 
                SUM(total_amount) as total_collected
            FROM orders
            WHERE status = 'completed'
            GROUP BY month_period, payment_method
            ORDER BY month_period DESC, total_collected DESC
        ");
        return $stmt->fetchAll();
    }
}
