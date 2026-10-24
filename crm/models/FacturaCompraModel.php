<?php
class FacturaCompraModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT fc.*, 
                       p.nombre AS proveedor_nombre, 
                       oc.id AS orden_id
                FROM facturas_compra fc
                LEFT JOIN proveedores p ON fc.proveedor_id = p.id
                LEFT JOIN ordenes_compra oc ON fc.orden_compra_id = oc.id
                ORDER BY fc.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM facturas_compra WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ? $res : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO facturas_compra (referencia_orden_compra, orden_compra_id, fecha, condicion, numero_factura, nro_timbrado, vencimiento_timbrado, proveedor_id, sucursal, observacion, producto, cantidad, costo_unit) VALUES (:referencia_orden_compra, :orden_compra_id, :fecha, :condicion, :numero_factura, :nro_timbrado, :vencimiento_timbrado, :proveedor_id, :sucursal, :observacion, :producto, :cantidad, :costo_unit)");
        return $stmt->execute([
            ':referencia_orden_compra' => $data['referencia_orden_compra'] ?: null,
            ':orden_compra_id' => $data['orden_compra_id'] ? (int)$data['orden_compra_id'] : null,
            ':fecha' => $data['fecha'] ?: '2026-08-12',
            ':condicion' => $data['condicion'] ?: 'CONTADO',
            ':numero_factura' => $data['numero_factura'] ?: '001-001-0000001',
            ':nro_timbrado' => $data['nro_timbrado'],
            ':vencimiento_timbrado' => $data['vencimiento_timbrado'] ?: '2026-08-12',
            ':proveedor_id' => (int)$data['proveedor_id'],
            ':sucursal' => $data['sucursal'],
            ':observacion' => $data['observacion'] ?: null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)($data['cantidad'] ?: 1),
            ':costo_unit' => (float)$data['costo_unit']
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE facturas_compra SET referencia_orden_compra = :referencia_orden_compra, orden_compra_id = :orden_compra_id, fecha = :fecha, condicion = :condicion, numero_factura = :numero_factura, nro_timbrado = :nro_timbrado, vencimiento_timbrado = :vencimiento_timbrado, proveedor_id = :proveedor_id, sucursal = :sucursal, observacion = :observacion, producto = :producto, cantidad = :cantidad, costo_unit = :costo_unit WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':referencia_orden_compra' => $data['referencia_orden_compra'] ?: null,
            ':orden_compra_id' => $data['orden_compra_id'] ? (int)$data['orden_compra_id'] : null,
            ':fecha' => $data['fecha'] ?: '2026-08-12',
            ':condicion' => $data['condicion'] ?: 'CONTADO',
            ':numero_factura' => $data['numero_factura'] ?: '001-001-0000001',
            ':nro_timbrado' => $data['nro_timbrado'],
            ':vencimiento_timbrado' => $data['vencimiento_timbrado'] ?: '2026-08-12',
            ':proveedor_id' => (int)$data['proveedor_id'],
            ':sucursal' => $data['sucursal'],
            ':observacion' => $data['observacion'] ?: null,
            ':producto' => $data['producto'],
            ':cantidad' => (int)($data['cantidad'] ?: 1),
            ':costo_unit' => (float)$data['costo_unit']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM facturas_compra WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
