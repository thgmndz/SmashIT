<?php
class PedidoCompraModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM pedidos_compra ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pedidos_compra WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO pedidos_compra (sucursal, observacion, producto, cantidad) VALUES (:sucursal, :observacion, :producto, :cantidad)");
        return $stmt->execute([
            ':sucursal' => $data['sucursal'],
            ':observacion' => $data['observacion'] ?? null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)$data['cantidad']
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE pedidos_compra SET sucursal = :sucursal, observacion = :observacion, producto = :producto, cantidad = :cantidad WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':sucursal' => $data['sucursal'],
            ':observacion' => $data['observacion'] ?? null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)$data['cantidad']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM pedidos_compra WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
