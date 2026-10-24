<?php
class PresupuestoCompraModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT pc.*, 
                       p.nombre AS proveedor_nombre, 
                       o.sucursal AS pedido_sucursal,
                       o.producto AS pedido_producto
                FROM presupuestos_compra pc
                LEFT JOIN proveedores p ON pc.proveedor_id = p.id
                LEFT JOIN pedidos_compra o ON pc.pedido_compra_id = o.id
                ORDER BY pc.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM presupuestos_compra WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO presupuestos_compra (referencia_pedido, pedido_compra_id, proveedor_id, sucursal, fecha_vencimiento, observacion, producto, cantidad, costo_unit) VALUES (:referencia_pedido, :pedido_compra_id, :proveedor_id, :sucursal, :fecha_vencimiento, :observacion, :producto, :cantidad, :costo_unit)");
        return $stmt->execute([
            ':referencia_pedido' => $data['referencia_pedido'] ?: null,
            ':pedido_compra_id' => $data['pedido_compra_id'] ? (int)$data['pedido_compra_id'] : null,
            ':proveedor_id' => (int)$data['proveedor_id'],
            ':sucursal' => $data['sucursal'],
            ':fecha_vencimiento' => $data['fecha_vencimiento'] ?: '2026-08-27',
            ':observacion' => $data['observacion'] ?: null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)($data['cantidad'] ?: 1),
            ':costo_unit' => (float)$data['costo_unit']
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE presupuestos_compra SET referencia_pedido = :referencia_pedido, pedido_compra_id = :pedido_compra_id, proveedor_id = :proveedor_id, sucursal = :sucursal, fecha_vencimiento = :fecha_vencimiento, observacion = :observacion, producto = :producto, cantidad = :cantidad, costo_unit = :costo_unit WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':referencia_pedido' => $data['referencia_pedido'] ?: null,
            ':pedido_compra_id' => $data['pedido_compra_id'] ? (int)$data['pedido_compra_id'] : null,
            ':proveedor_id' => (int)$data['proveedor_id'],
            ':sucursal' => $data['sucursal'],
            ':fecha_vencimiento' => $data['fecha_vencimiento'] ?: '2026-08-27',
            ':observacion' => $data['observacion'] ?: null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)($data['cantidad'] ?: 1),
            ':costo_unit' => (float)$data['costo_unit']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM presupuestos_compra WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
