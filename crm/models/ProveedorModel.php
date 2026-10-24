<?php
class ProveedorModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM proveedores ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO proveedores (nombre, ruc, telefono, categoria_producto) VALUES (:nombre, :ruc, :telefono, :categoria_producto)");
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':ruc' => $data['ruc'],
            ':telefono' => $data['telefono'],
            ':categoria_producto' => $data['categoria_producto']
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE proveedores SET nombre = :nombre, ruc = :ruc, telefono = :telefono, categoria_producto = :categoria_producto WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':ruc' => $data['ruc'],
            ':telefono' => $data['telefono'],
            ':categoria_producto' => $data['categoria_producto']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
