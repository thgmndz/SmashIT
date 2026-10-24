<?php
class OrdenCompraModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT oc.*, 
                       p.nombre AS proveedor_nombre, 
                       e.name AS funcionario_nombre,
                       pre.referencia_pedido AS presupuesto_ref
                FROM ordenes_compra oc
                LEFT JOIN proveedores p ON oc.proveedor_id = p.id
                LEFT JOIN employees e ON oc.funcionario_id = e.id
                LEFT JOIN presupuestos_compra pre ON oc.presupuesto_compra_id = pre.id
                ORDER BY oc.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM ordenes_compra WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO ordenes_compra (referencia_presupuesto, presupuesto_compra_id, fecha, sucursal, proveedor_id, funcionario_id, observacion, producto, cantidad, costo_unit) VALUES (:referencia_presupuesto, :presupuesto_compra_id, :fecha, :sucursal, :proveedor_id, :funcionario_id, :observacion, :producto, :cantidad, :costo_unit)");
        return $stmt->execute([
            ':referencia_presupuesto' => $data['referencia_presupuesto'] ?: null,
            ':presupuesto_compra_id' => $data['presupuesto_compra_id'] ? (int)$data['presupuesto_compra_id'] : null,
            ':fecha' => $data['fecha'] ?: '2026-08-12',
            ':sucursal' => $data['sucursal'],
            ':proveedor_id' => (int)$data['proveedor_id'],
            ':funcionario_id' => (int)$data['funcionario_id'],
            ':observacion' => $data['observacion'] ?: null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)($data['cantidad'] ?: 1),
            ':costo_unit' => (float)$data['costo_unit']
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE ordenes_compra SET referencia_presupuesto = :referencia_presupuesto, presupuesto_compra_id = :presupuesto_compra_id, fecha = :fecha, sucursal = :sucursal, proveedor_id = :proveedor_id, funcionario_id = :funcionario_id, observacion = :observacion, producto = :producto, cantidad = :cantidad, costo_unit = :costo_unit WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':referencia_presupuesto' => $data['referencia_presupuesto'] ?: null,
            ':presupuesto_compra_id' => $data['presupuesto_compra_id'] ? (int)$data['presupuesto_compra_id'] : null,
            ':fecha' => $data['fecha'] ?: '2026-08-12',
            ':sucursal' => $data['sucursal'],
            ':proveedor_id' => (int)$data['proveedor_id'],
            ':funcionario_id' => (int)$data['funcionario_id'],
            ':observacion' => $data['observacion'] ?: null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)($data['cantidad'] ?: 1),
            ':costo_unit' => (float)$data['costo_unit']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM ordenes_compra WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
