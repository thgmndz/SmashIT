<?php
class DeliveryDriverModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM delivery_drivers ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function getActive(): array {
        $stmt = $this->db->query("SELECT * FROM delivery_drivers WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM delivery_drivers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $driver = $stmt->fetch();
        return $driver ? $driver : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO delivery_drivers (nombre, telefono, vehiculo, chapa, activo) 
            VALUES (:nombre, :telefono, :vehiculo, :chapa, :activo)
        ");
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':vehiculo' => $data['vehiculo'],
            ':chapa' => $data['chapa'],
            ':activo' => (int)($data['activo'] ?? 1)
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE delivery_drivers 
            SET nombre = :nombre, 
                telefono = :telefono, 
                vehiculo = :vehiculo, 
                chapa = :chapa, 
                activo = :activo 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':vehiculo' => $data['vehiculo'],
            ':chapa' => $data['chapa'],
            ':activo' => (int)($data['activo'] ?? 1)
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM delivery_drivers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getDriverMonthlyReport(): array {
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');

        $stmt = $this->db->prepare("
            SELECT 
                dd.nombre as driver_name,
                dd.telefono as driver_phone,
                dd.vehiculo as driver_vehicle,
                COUNT(o.id) as total_deliveries,
                COALESCE(SUM(o.total_amount), 0.00) as total_amount
            FROM delivery_drivers dd
            LEFT JOIN orders o ON o.delivery_driver_id = dd.id 
                AND o.delivery_type = 'delivery'
                AND o.status = 'completed'
                AND o.created_at BETWEEN :start_date AND :end_date
            GROUP BY dd.id, dd.nombre, dd.telefono, dd.vehiculo
            ORDER BY total_deliveries DESC, dd.nombre ASC
        ");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll();
    }
}
