<?php
class RecaudacionModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT rd.*, 
                       cr.opened_at AS caja_fecha_apertura, 
                       e.name AS funcionario_nombre
                FROM recaudaciones_depositar rd
                LEFT JOIN cash_registers cr ON rd.cash_register_id = cr.id
                LEFT JOIN employees e ON cr.employee_id = e.id
                ORDER BY rd.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM recaudaciones_depositar WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO recaudaciones_depositar (cash_register_id, monto_depositar, banco, nro_boleta, observacion, estado) 
            VALUES (:cash_register_id, :monto_depositar, :banco, :nro_boleta, :observacion, :estado)
        ");
        return $stmt->execute([
            ':cash_register_id' => (int)$data['cash_register_id'],
            ':monto_depositar' => (float)$data['monto_depositar'],
            ':banco' => $data['banco'],
            ':nro_boleta' => $data['nro_boleta'],
            ':observacion' => $data['observacion'] ?: null,
            ':estado' => $data['estado'] ?: 'pendiente'
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE recaudaciones_depositar 
            SET cash_register_id = :cash_register_id, 
                monto_depositar = :monto_depositar, 
                banco = :banco, 
                nro_boleta = :nro_boleta, 
                observacion = :observacion, 
                estado = :estado 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':cash_register_id' => (int)$data['cash_register_id'],
            ':monto_depositar' => (float)$data['monto_depositar'],
            ':banco' => $data['banco'],
            ':nro_boleta' => $data['nro_boleta'],
            ':observacion' => $data['observacion'] ?: null,
            ':estado' => $data['estado']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM recaudaciones_depositar WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
