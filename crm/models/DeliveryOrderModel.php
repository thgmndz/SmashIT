<?php
class DeliveryOrderModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getDeliveryOrders(): array {
        // Query orders that have delivery_type = 'delivery' and status = 'ready'
        $stmt = $this->db->query("
            SELECT o.*, dd.nombre AS driver_name, dd.telefono AS driver_phone
            FROM orders o
            LEFT JOIN delivery_drivers dd ON o.delivery_driver_id = dd.id
            WHERE o.delivery_type = 'delivery' AND o.status = 'ready'
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function assignDriver(int $orderId, int $driverId): bool {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET delivery_driver_id = :driver_id, 
                delivery_status = 'en_camino' 
            WHERE id = :order_id
        ");
        return $stmt->execute([
            ':order_id' => $orderId,
            ':driver_id' => $driverId
        ]);
    }

    public function updateDeliveryStatus(int $orderId, string $status): bool {
        // Si se marca como entregado, el estado de la orden pasa a 'completed'
        if ($status === 'entregado') {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET delivery_status = :delivery_status,
                    status = 'completed'
                WHERE id = :order_id
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET delivery_status = :delivery_status 
                WHERE id = :order_id
            ");
        }
        return $stmt->execute([
            ':order_id' => $orderId,
            ':delivery_status' => $status
        ]);
    }
}
