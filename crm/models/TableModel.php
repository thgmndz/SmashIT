<?php
class TableModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM restaurant_tables ORDER BY table_number ASC");
        $tables = $stmt->fetchAll();
        $today = date('Y-m-d');
        foreach ($tables as &$table) {
            $table['db_status'] = $table['status'];
            if ($table['status'] === 'reservado') {
                if (!empty($table['reservation_datetime'])) {
                    $resDate = date('Y-m-d', strtotime($table['reservation_datetime']));
                    if ($resDate !== $today) {
                        $table['status'] = 'libre';
                    }
                }
            }
        }
        return $tables;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM restaurant_tables WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $table = $stmt->fetch();
        return $table ? $table : null;
    }

    public function updateStatus(int $id, string $status, ?string $orderCode = null, ?string $resName = null, ?string $resCi = null, ?string $resDatetime = null): bool {
        $stmt = $this->db->prepare("
            UPDATE restaurant_tables 
            SET status = :status, 
                current_order_code = :order_code, 
                reservation_name = :res_name, 
                reservation_ci = :res_ci, 
                reservation_datetime = :res_datetime, 
                updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':order_code' => $orderCode,
            ':res_name' => $resName,
            ':res_ci' => $resCi,
            ':res_datetime' => $resDatetime
        ]);
    }

    public function create(int $tableNumber, int $capacity): bool {
        $stmt = $this->db->prepare("INSERT INTO restaurant_tables (table_number, capacity, status) VALUES (:table_number, :capacity, 'libre')");
        return $stmt->execute([
            ':table_number' => $tableNumber,
            ':capacity' => $capacity
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM restaurant_tables WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getTotalCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM restaurant_tables")->fetchColumn();
    }
}
