<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Verificar autenticación
AuthController::checkAuth();

$id = (int)($_GET['id'] ?? 0);
$type = trim($_GET['type'] ?? '');

if ($id <= 0 || empty($type)) {
    die("ID o tipo de impresión inválido.");
}

try {
    $db = CRMDatabase::getConnection();
    $data = null;
    $title = "";

    switch ($type) {
        case 'proveedor':
            $stmt = $db->prepare("SELECT * FROM proveedores WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Ficha de Proveedor";
            break;
            
        case 'pedido':
            $stmt = $db->prepare("SELECT * FROM pedidos_compra WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Pedido de Compra";
            break;
            
        case 'presupuesto':
            $stmt = $db->prepare("
                SELECT pc.*, p.nombre AS proveedor_nombre, p.ruc AS proveedor_ruc, p.telefono AS proveedor_telefono
                FROM presupuestos_compra pc
                LEFT JOIN proveedores p ON pc.proveedor_id = p.id
                WHERE pc.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Presupuesto de Compra";
            break;
            
        case 'orden':
            $stmt = $db->prepare("
                SELECT oc.*, p.nombre AS proveedor_nombre, p.ruc AS proveedor_ruc, e.name AS funcionario_nombre
                FROM ordenes_compra oc
                LEFT JOIN proveedores p ON oc.proveedor_id = p.id
                LEFT JOIN employees e ON oc.funcionario_id = e.id
                WHERE oc.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Orden de Compra";
            break;
            
        case 'factura':
            $stmt = $db->prepare("
                SELECT fc.*, p.nombre AS proveedor_nombre, p.ruc AS proveedor_ruc
                FROM facturas_compra fc
                LEFT JOIN proveedores p ON fc.proveedor_id = p.id
                WHERE fc.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Factura de Compra";
            break;

        case 'caja':
            $stmt = $db->prepare("
                SELECT cr.*, e.name AS employee_name
                FROM cash_registers cr
                LEFT JOIN employees e ON cr.employee_id = e.id
                WHERE cr.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Apertura y Cierre de Caja";
            break;
            
        case 'arqueo':
            $stmt = $db->prepare("
                SELECT ac.*, cr.opened_at, e.name AS employee_name
                FROM arqueos_caja ac
                LEFT JOIN cash_registers cr ON ac.cash_register_id = cr.id
                LEFT JOIN employees e ON cr.employee_id = e.id
                WHERE ac.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Arqueo de Caja";
            break;
            
        case 'recaudacion':
            $stmt = $db->prepare("
                SELECT rd.*, cr.opened_at, e.name AS employee_name
                FROM recaudaciones_depositar rd
                LEFT JOIN cash_registers cr ON rd.cash_register_id = cr.id
                LEFT JOIN employees e ON cr.employee_id = e.id
                WHERE rd.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();
            $title = "Recaudación a Depositar";
            break;
            
        default:
            die("Tipo de impresión no soportado.");
    }

    if (!$data) {
        die("No se encontraron datos para la impresión.");
    }

} catch (Exception $e) {
    die("Error al consultar datos de impresión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión - <?php echo $title; ?> #<?php echo $id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            border-radius: 8px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 10px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-4 { margin-top: 25px; }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .divider {
            border-top: 2px solid #333;
            margin: 15px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 4px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
            color: #555;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .btn-print {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
            .container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="text-center">
    <button class="btn-print" onclick="window.print()">Imprimir Documento</button>
</div>

<div class="container">
    <div class="text-center mb-4">
        <div class="header-title">SmashIT Burgers</div>
        <div>Panel CRM & Administración de Compras</div>
        <div class="fw-bold mt-4" style="font-size: 18px; color: #333;"><?php echo $title; ?></div>
    </div>
    
    <div class="divider"></div>

    <table class="info-table">
        <?php if ($type === 'proveedor'): ?>
            <tr>
                <td class="info-label">Nombre del Proveedor:</td>
                <td><?php echo htmlspecialchars($data['nombre']); ?></td>
            </tr>
            <tr>
                <td class="info-label">RUC:</td>
                <td><?php echo htmlspecialchars($data['ruc']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Teléfono:</td>
                <td><?php echo htmlspecialchars($data['telefono']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Categoría de Producto:</td>
                <td><?php echo htmlspecialchars($data['categoria_producto']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Fecha de Registro:</td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($data['created_at'])); ?></td>
            </tr>

        <?php elseif ($type === 'pedido'): ?>
            <tr>
                <td class="info-label">Sucursal:</td>
                <td><?php echo htmlspecialchars($data['sucursal']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Fecha del Pedido:</td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($data['created_at'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Producto Solicitado:</td>
                <td><?php echo htmlspecialchars($data['producto']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Cantidad:</td>
                <td><?php echo (int)$data['cantidad']; ?></td>
            </tr>
            <tr>
                <td class="info-label">Observación:</td>
                <td><?php echo htmlspecialchars($data['observacion'] ?: 'Sin observaciones'); ?></td>
            </tr>

        <?php elseif ($type === 'presupuesto'): ?>
            <tr>
                <td class="info-label">Referencia Pedido:</td>
                <td><?php echo htmlspecialchars($data['referencia_pedido'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Proveedor:</td>
                <td><?php echo htmlspecialchars($data['proveedor_nombre']); ?> (RUC: <?php echo htmlspecialchars($data['proveedor_ruc']); ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Sucursal:</td>
                <td><?php echo htmlspecialchars($data['sucursal']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Vencimiento del Presupuesto:</td>
                <td><?php echo date('d/m/Y', strtotime($data['fecha_vencimiento'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Observación:</td>
                <td><?php echo htmlspecialchars($data['observacion'] ?: 'Sin observaciones'); ?></td>
            </tr>

        <?php elseif ($type === 'orden'): ?>
            <tr>
                <td class="info-label">Referencia Presupuesto:</td>
                <td><?php echo htmlspecialchars($data['referencia_presupuesto'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Fecha de Emisión:</td>
                <td><?php echo date('d/m/Y', strtotime($data['fecha'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Proveedor:</td>
                <td><?php echo htmlspecialchars($data['proveedor_nombre']); ?> (RUC: <?php echo htmlspecialchars($data['proveedor_ruc']); ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Sucursal:</td>
                <td><?php echo htmlspecialchars($data['sucursal']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Funcionario Emisor:</td>
                <td><?php echo htmlspecialchars($data['funcionario_nombre']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Observación:</td>
                <td><?php echo htmlspecialchars($data['observacion'] ?: 'Sin observaciones'); ?></td>
            </tr>

        <?php elseif ($type === 'factura'): ?>
            <tr>
                <td class="info-label">Número Factura:</td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($data['numero_factura']); ?></span></td>
            </tr>
            <tr>
                <td class="info-label">Nro. Timbrado:</td>
                <td><?php echo htmlspecialchars($data['nro_timbrado']); ?> (Vence: <?php echo date('d/m/Y', strtotime($data['vencimiento_timbrado'])); ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Fecha Emisión:</td>
                <td><?php echo date('d/m/Y', strtotime($data['fecha'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Condición de Pago:</td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($data['condicion']); ?></span></td>
            </tr>
            <tr>
                <td class="info-label">Proveedor:</td>
                <td><?php echo htmlspecialchars($data['proveedor_nombre']); ?> (RUC: <?php echo htmlspecialchars($data['proveedor_ruc']); ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Sucursal:</td>
                <td><?php echo htmlspecialchars($data['sucursal']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Referencia Orden:</td>
                <td><?php echo htmlspecialchars($data['referencia_orden_compra'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Observación:</td>
                <td><?php echo htmlspecialchars($data['observacion'] ?: 'Sin observaciones'); ?></td>
            </tr>

        <?php elseif ($type === 'caja'): ?>
            <tr>
                <td class="info-label">Caja ID:</td>
                <td>Caja #<?php echo $data['id']; ?></td>
            </tr>
            <tr>
                <td class="info-label">Cajero / Funcionario:</td>
                <td><?php echo htmlspecialchars($data['employee_name'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Monto Apertura:</td>
                <td><?php echo number_format($data['opening_amount'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Monto Cierre:</td>
                <td><?php echo $data['closing_amount'] !== null ? number_format($data['closing_amount'], 0, ',', '.') . ' Gs.' : 'Abierta'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Monto Calculado Sistema:</td>
                <td><?php echo $data['calculated_amount'] !== null ? number_format($data['calculated_amount'], 0, ',', '.') . ' Gs.' : 'Abierta'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Diferencia:</td>
                <td class="fw-bold <?php echo ($data['difference'] ?? 0) < 0 ? 'text-danger' : ''; ?>"><?php echo $data['difference'] !== null ? number_format($data['difference'], 0, ',', '.') . ' Gs.' : 'Abierta'; ?></td>
            </tr>
            <tr>
                <td class="info-label">Estado:</td>
                <td><?php echo strtoupper($data['status']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Apertura:</td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($data['opened_at'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Cierre:</td>
                <td><?php echo $data['closed_at'] ? date('d/m/Y H:i:s', strtotime($data['closed_at'])) : 'Abierta'; ?></td>
            </tr>

        <?php elseif ($type === 'arqueo'): ?>
            <tr>
                <td class="info-label">Arqueo ID:</td>
                <td>Arqueo #<?php echo $data['id']; ?></td>
            </tr>
            <tr>
                <td class="info-label">Caja Vinculada:</td>
                <td>Caja #<?php echo $data['cash_register_id']; ?> (Abierta el <?php echo date('d/m/Y H:i', strtotime($data['opened_at'])); ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Cajero / Funcionario:</td>
                <td><?php echo htmlspecialchars($data['employee_name'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Fecha Arqueo:</td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($data['fecha'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Efectivo Declarado:</td>
                <td><?php echo number_format($data['efectivo_declarado'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Tarjeta Declarado:</td>
                <td><?php echo number_format($data['tarjeta_declarado'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Cheque Declarado:</td>
                <td><?php echo number_format($data['cheque_declarado'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Declarado Total:</td>
                <td><?php echo number_format($data['efectivo_declarado'] + $data['tarjeta_declarado'] + $data['cheque_declarado'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Monto Teórico Sistema:</td>
                <td><?php echo number_format($data['monto_sistema'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Diferencia Arqueo:</td>
                <td class="fw-bold <?php echo $data['diferencia'] < 0 ? 'text-danger' : ''; ?>"><?php echo number_format($data['diferencia'], 0, ',', '.'); ?> Gs.</td>
            </tr>
            <tr>
                <td class="info-label">Observación:</td>
                <td><?php echo htmlspecialchars($data['observacion'] ?: 'Sin observaciones'); ?></td>
            </tr>

        <?php elseif ($type === 'recaudacion'): ?>
            <tr>
                <td class="info-label">Registro ID:</td>
                <td>Reg. #<?php echo $data['id']; ?></td>
            </tr>
            <tr>
                <td class="info-label">Caja de Origen:</td>
                <td>Caja #<?php echo $data['cash_register_id']; ?> (Abierta el <?php echo date('d/m/Y H:i', strtotime($data['opened_at'])); ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Funcionario depositante:</td>
                <td><?php echo htmlspecialchars($data['employee_name'] ?: 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="info-label">Fecha Registro:</td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($data['fecha'])); ?></td>
            </tr>
            <tr>
                <td class="info-label">Banco Destinatario:</td>
                <td><?php echo htmlspecialchars($data['banco']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Número de Boleta:</td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($data['nro_boleta']); ?></span></td>
            </tr>
            <tr>
                <td class="info-label">Monto a Depositar:</td>
                <td><span class="fw-bold"><?php echo number_format($data['monto_depositar'], 0, ',', '.'); ?> Gs.</span></td>
            </tr>
            <tr>
                <td class="info-label">Estado de Depósito:</td>
                <td><?php echo strtoupper($data['estado']); ?></td>
            </tr>
            <tr>
                <td class="info-label">Observación:</td>
                <td><?php echo htmlspecialchars($data['observacion'] ?: 'Sin observaciones'); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <?php if (in_array($type, ['presupuesto', 'orden', 'factura'])): ?>
        <div class="fw-bold mt-4 mb-2">Detalle de Productos / Costos:</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center" style="width: 100px;">Cantidad</th>
                    <th class="text-end" style="width: 150px;">Costo Unitario</th>
                    <th class="text-end" style="width: 180px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($data['producto']); ?></td>
                    <td class="text-center"><?php echo (int)$data['cantidad']; ?></td>
                    <td class="text-end"><?php echo number_format($data['costo_unit'], 0, ',', '.'); ?> Gs.</td>
                    <td class="text-end fw-bold"><?php echo number_format($data['cantidad'] * $data['costo_unit'], 0, ',', '.'); ?> Gs.</td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="mt-4 text-center" style="font-size: 11px; color: #888; margin-top: 50px;">
        Documento generado el <?php echo date('d/m/Y H:i:s'); ?> para uso administrativo interno. SmashIT Burgers ERP.
    </div>
</div>

</body>
</html>
