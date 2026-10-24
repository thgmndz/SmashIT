<?php
class ArqueoCajaModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT ac.*, 
                       cr.opened_at AS caja_fecha_apertura, 
                       e.name AS funcionario_nombre
                FROM arqueos_caja ac
                LEFT JOIN cash_registers cr ON ac.cash_register_id = cr.id
                LEFT JOIN employees e ON cr.employee_id = e.id
                ORDER BY ac.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM arqueos_caja WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO arqueos_caja (cash_register_id, efectivo_declarado, tarjeta_declarado, cheque_declarado, monto_sistema, diferencia, observacion) 
            VALUES (:cash_register_id, :efectivo_declarado, :tarjeta_declarado, :cheque_declarado, :monto_sistema, :diferencia, :observacion)
        ");
        return $stmt->execute([
            ':cash_register_id' => (int)$data['cash_register_id'],
            ':efectivo_declarado' => (float)$data['efectivo_declarado'],
            ':tarjeta_declarado' => (float)$data['tarjeta_declarado'],
            ':cheque_declarado' => (float)$data['cheque_declarado'],
            ':monto_sistema' => (float)$data['monto_sistema'],
            ':diferencia' => (float)$data['diferencia'],
            ':observacion' => $data['observacion'] ?: null
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE arqueos_caja 
            SET cash_register_id = :cash_register_id, 
                efectivo_declarado = :efectivo_declarado, 
                tarjeta_declarado = :tarjeta_declarado, 
                cheque_declarado = :cheque_declarado, 
                monto_sistema = :monto_sistema, 
                diferencia = :diferencia, 
                observacion = :observacion 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':cash_register_id' => (int)$data['cash_register_id'],
            ':efectivo_declarado' => (float)$data['efectivo_declarado'],
            ':tarjeta_declarado' => (float)$data['tarjeta_declarado'],
            ':cheque_declarado' => (float)$data['cheque_declarado'],
            ':monto_sistema' => (float)$data['monto_sistema'],
            ':diferencia' => (float)$data['diferencia'],
            ':observacion' => $data['observacion'] ?: null
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM arqueos_caja WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
