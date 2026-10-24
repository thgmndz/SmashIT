<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Verificar autenticación
AuthController::checkAuth();

$orderId = (int)($_GET['id'] ?? 0);
$printType = trim($_GET['type'] ?? 'ticket');

if ($orderId <= 0) {
    die("ID de pedido inválido.");
}

try {
    $db = CRMDatabase::getConnection();

    // Obtener cabecera de pedido
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        die("No se encontró el pedido solicitado.");
    }

    // Obtener detalles del pedido
    $stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
    $stmtItems->execute([':order_id' => $orderId]);
    $items = $stmtItems->fetchAll();

} catch (Exception $e) {
    die("Error al consultar datos de impresión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Comprobante - <?php echo htmlspecialchars($order['order_code']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-4 { margin-top: 20px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px;
            text-align: left;
        }

        /* -------------------------------------------
           1. ESTILOS TICKET TÉRMICO (80mm)
        ------------------------------------------- */
        <?php if ($printType === 'ticket'): ?>
        body {
            padding: 5px;
            width: 280px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
        }
        .ticket-header {
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .ticket-divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .ticket-table th, .ticket-table td {
            padding: 4px 0;
            font-size: 11px;
        }
        <?php endif; ?>

        /* -------------------------------------------
           2. ESTILOS COMPROBANTES A4 (Recibo, Autoimpresa, KUDE)
        ------------------------------------------- */
        <?php if ($printType !== 'ticket'): ?>
        .a4-container {
            width: 720px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            border-radius: 8px;
        }
        .a4-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .a4-logo {
            font-size: 24px;
            font-weight: bold;
            color: #e28743;
        }
        .a4-meta-box {
            border: 1px solid #000;
            padding: 15px;
            border-radius: 5px;
            min-width: 250px;
            background-color: #f9f9f9;
        }
        .a4-table th {
            background-color: #eee;
            border: 1px solid #ccc;
        }
        .a4-table td {
            border-bottom: 1px solid #ddd;
            border-left: 1px solid #eee;
            border-right: 1px solid #eee;
        }
        .a4-summary-table td {
            padding: 4px 8px;
        }
        .kude-badge {
            background-color: #000;
            color: #fff;
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 3px;
            display: inline-block;
        }
        <?php endif; ?>

        /* Regla de impresión */
        @media print {
            body { padding: 0; margin: 0; }
            .a4-container { border: none; padding: 0; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print mb-4 text-center">
        <button onclick="window.print();" style="padding: 10px 25px; background: #28a745; color: #white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; color: white;">
            🖨️ Imprimir Comprobante
        </button>
        <button onclick="window.close();" style="padding: 10px 25px; background: #6c757d; color: #white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; color: white; margin-left: 10px;">
            Cerrar Ventana
        </button>
    </div>

    <!-- RENDERIZAR TICKET TERMICO (80mm) -->
    <?php if ($printType === 'ticket'): ?>
        <div class="ticket-container">
            <div class="ticket-header text-center">
                <h3 style="margin: 0 0 5px 0; font-size: 15px;">SMASHIT BURGERS</h3>
                <p class="mb-1">De: Thiago Mendez</p>
                <p class="mb-1">Dirección: Av. Mariscal López 123</p>
                <p class="mb-1">Teléfono: 0981 999 888</p>
                <p class="mb-1" style="font-size: 10px;">RUC: 80012345-0</p>
            </div>
            
            <div class="mb-2">
                <div><strong>COMPROBANTE:</strong> TICKET FACTURA</div>
                <div><strong>CÓDIGO:</strong> <?php echo htmlspecialchars($order['order_code']); ?></div>
                <div><strong>FECHA:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                <div><strong>CAJERO:</strong> Admin CRM</div>
            </div>

            <div class="ticket-divider"></div>

            <div class="mb-2">
                <div><strong>CLIENTE:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                <div><strong>DETALLE:</strong> <?php echo htmlspecialchars($order['notes'] ?: 'Consumo en Local'); ?></div>
            </div>

            <div class="ticket-divider"></div>

            <table class="ticket-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Item</th>
                        <th class="text-center" style="width: 15%;">Cant</th>
                        <th class="text-end" style="width: 35%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="ticket-divider"></div>

            <table class="ticket-table fw-bold">
                <tr>
                    <td>SUBTOTAL:</td>
                    <td class="text-end"><?php echo number_format($order['subtotal'], 0, ',', '.'); ?> Gs.</td>
                </tr>
                <tr>
                    <td>ENVÍO:</td>
                    <td class="text-end"><?php echo number_format($order['delivery_fee'], 0, ',', '.'); ?> Gs.</td>
                </tr>
                <tr style="font-size: 12px;">
                    <td>TOTAL NETO:</td>
                    <td class="text-end"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> Gs.</td>
                </tr>
            </table>

            <div class="ticket-divider"></div>

            <div class="text-center mb-1">
                <strong>Pago: <?php echo htmlspecialchars($order['payment_method']); ?></strong>
            </div>
            <div class="text-center" style="font-size: 10px; margin-top: 15px;">
                ¡Gracias por tu preferencia!
                <br>Smashed to Perfection 🔥
            </div>
        </div>
    <?php endif; ?>

    <!-- RENDERIZAR FORMATOS A4 (Recibo, Autoimpresa, Electrónica) -->
    <?php if ($printType !== 'ticket'): ?>
        <div class="a4-container">
            <div class="a4-header">
                <div>
                    <div class="a4-logo">SMASHIT <span style="color: #000;">BURGERS</span></div>
                    <p class="mb-1" style="margin-top: 8px;"><strong>De:</strong> Thiago Mendez</p>
                    <p class="mb-1"><strong>Dirección:</strong> Av. Mariscal López 123, Asunción</p>
                    <p class="mb-1"><strong>Teléfono:</strong> 0981 999 888 | <strong>Email:</strong> crm@smashit.com</p>
                </div>
                <div class="a4-meta-box">
                    <?php if ($printType === 'recibo'): ?>
                        <h4 class="text-center" style="margin: 0 0 10px 0; color: #555;">RECIBO DE DINERO</h4>
                        <div class="text-center fw-bold fs-5" style="color: #e28743;"><?php echo htmlspecialchars($order['order_code']); ?></div>
                    <?php elseif ($printType === 'autoimpresa'): ?>
                        <h4 class="text-center" style="margin: 0 0 10px 0; color: #000;">FACTURA AUTOIMPRESA</h4>
                        <div class="small mb-1"><strong>RUC:</strong> 80012345-0</div>
                        <div class="small mb-1"><strong>Nro Factura:</strong> 001-001-000<?php echo $order['id']; ?></div>
                        <div class="small"><strong>Timbrado Nro:</strong> 12345678 (Vence: 31/12/2026)</div>
                    <?php elseif ($printType === 'electronica'): ?>
                        <h4 class="text-center" style="margin: 0 0 10px 0; color: #008080;">FACTURA ELECTRÓNICA</h4>
                        <div class="mb-2 text-center"><span class="kude-badge">REPRESENTACIÓN GRÁFICA - KUDE</span></div>
                        <div class="small mb-1"><strong>CDC Code:</strong> 0120260806<?php echo str_pad($order['id'], 34, "0", STR_PAD_LEFT); ?></div>
                        <div class="small"><strong>RUC Emisor:</strong> 80012345-0</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-4" style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #fafafa;">
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        <div class="mb-1"><strong>Señor(es):</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                        <div class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?> hs</div>
                        <div><strong>Nota / Observación:</strong> <?php echo htmlspecialchars($order['notes'] ?: 'Ninguna'); ?></div>
                    </div>
                    <div>
                        <div class="mb-1"><strong>Método de Pago:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
                        <div class="mb-1"><strong>Tipo de Pedido:</strong> <?php echo $order['delivery_type'] === 'dinein' ? 'Consumo en Local' : ($order['delivery_type'] === 'pickup' ? 'Retiro en Local' : 'Delivery'); ?></div>
                    </div>
                </div>
            </div>

            <table class="a4-table mb-4">
                <thead>
                    <tr>
                        <th style="width: 10%;" class="text-center">Cant</th>
                        <th style="width: 55%;">Descripción de Producto</th>
                        <th style="width: 15%;" class="text-end">Precio Unit.</th>
                        <th style="width: 20%;" class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="text-end"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?> Gs.</td>
                            <td class="text-end"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> Gs.</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: flex-end;">
                <table class="a4-summary-table fw-bold" style="width: 300px;">
                    <tr>
                        <td class="text-muted">Subtotal:</td>
                        <td class="text-end"><?php echo number_format($order['subtotal'], 0, ',', '.'); ?> Gs.</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cargo Entrega:</td>
                        <td class="text-end"><?php echo number_format($order['delivery_fee'], 0, ',', '.'); ?> Gs.</td>
                    </tr>
                    <tr style="font-size: 15px; border-top: 1.5px solid #000;">
                        <td style="color: #e28743; padding-top: 8px;">TOTAL GENERAL:</td>
                        <td class="text-end text-success" style="padding-top: 8px;"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> Gs.</td>
                    </tr>
                </table>
            </div>

            <?php if ($printType === 'electronica'): ?>
                <div class="mt-4 p-3 text-center text-muted" style="border: 1px dashed #ccc; border-radius: 5px; font-size: 11px; background-color: #fbfbfb;">
                    <div class="fw-bold mb-1" style="color:#008080;">ESTA ES UNA REPRESENTACIÓN GRÁFICA DE UN COMPROBANTE ELECTRÓNICO (KUDE)</div>
                    Consulte la validez de esta Factura Electrónica ingresando al portal del Ministerio de Hacienda con el código CDC proveído en la cabecera.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script>
        // Auto-abrir la ventana de impresión nativa del navegador al cargar la página
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
