<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Verificar autenticación
AuthController::checkAuth();

$invoiceId = (int)($_GET['id'] ?? 0);
$printType = trim($_GET['type'] ?? 'autoimpresa');

if ($invoiceId <= 0) {
    die("ID de factura inválido.");
}

try {
    $db = CRMDatabase::getConnection();

    // Obtener cabecera de la factura
    $stmt = $db->prepare("
        SELECT i.*, c.name AS customer_name, c.phone AS customer_phone, c.ruc_ci AS customer_ruc, c.address AS customer_address, o.delivery_address AS order_address
        FROM invoices i 
        JOIN customers c ON i.customer_id = c.id 
        LEFT JOIN orders o ON i.order_id = o.id
        WHERE i.id = :id
    ");
    $stmt->execute([':id' => $invoiceId]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        die("No se encontró la factura solicitada.");
    }

    // Obtener detalles de la factura
    $stmtItems = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :invoice_id");
    $stmtItems->execute([':invoice_id' => $invoiceId]);
    $items = $stmtItems->fetchAll();

} catch (Exception $e) {
    die("Error al consultar datos de impresión: " . $e->getMessage());
}

// Función simple para convertir números a palabras en Español (Guaraníes)
function numeroAPalabras($numero) {
    $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = [11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SIETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    if ($numero == 0) return 'CERO';
    if ($numero == 100) return 'CIEN';

    $palabras = '';

    // Millones
    if ($numero >= 1000000) {
        $millones = floor($numero / 1000000);
        $numero = $numero % 1000000;
        if ($millones == 1) {
            $palabras .= 'UN MILLÓN ';
        } else {
            $palabras .= numeroAPalabras($millones) . ' MILLONES ';
        }
    }

    // Miles
    if ($numero >= 1000) {
        $miles = floor($numero / 1000);
        $numero = $numero % 1000;
        if ($miles == 1) {
            $palabras .= 'MIL ';
        } else {
            $palabras .= numeroAPalabras($miles) . ' MIL ';
        }
    }

    // Centenas
    if ($numero >= 100) {
        $cent = floor($numero / 100);
        $numero = $numero % 100;
        $palabras .= $centenas[$cent] . ' ';
    }

    // Decenas e Unidades
    if ($numero > 0) {
        if ($numero < 10) {
            $palabras .= $unidades[$numero];
        } elseif (isset($especiales[$numero])) {
            $palabras .= $especiales[$numero];
        } else {
            $dec = floor($numero / 10);
            $uni = $numero % 10;
            $palabras .= $decenas[$dec];
            if ($uni > 0) {
                $palabras .= ' Y ' . $unidades[$uni];
            }
        }
    }

    return trim($palabras);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Comprobante - <?php echo htmlspecialchars($invoice['invoice_number']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        
        /* Estilos generales de tabla */
        table {
            width: 100%;
            border-collapse: collapse;
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
            text-align: left;
        }
        .ticket-table th {
            font-weight: bold;
        }
        <?php endif; ?>

        /* -------------------------------------------
           2. ESTILOS FORMATO A4
        ------------------------------------------- */
        <?php if ($printType !== 'ticket'): ?>
        .invoice-container {
            width: 760px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
            box-sizing: border-box;
        }
        .invoice-header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
        }
        .invoice-header-table td {
            vertical-align: top;
            padding: 5px;
        }
        .logo-box {
            font-size: 20px;
            font-weight: bold;
            color: #e28743;
        }
        .meta-box {
            border: 1px solid #000;
            padding: 10px;
            background-color: #fff;
            text-align: center;
        }
        .customer-table {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .customer-table td {
            padding: 5px 8px;
            border: 1px solid #000;
        }
        .items-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 8px;
            border: 1px solid #000;
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

        .no-print-btn {
            padding: 10px 25px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .no-print-btn-close {
            padding: 10px 25px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            .invoice-container { border: 2px solid #000; }
        }
    </style>
</head>
<body>

    <div class="no-print mb-4 text-center">
        <button class="no-print-btn" onclick="window.print();">🖨️ Imprimir Comprobante</button>
        <button class="no-print-btn-close" onclick="window.close();">Cerrar</button>
    </div>

    <!-- RENDERIZAR TICKET TÉRMICO (80mm) -->
    <?php if ($printType === 'ticket'): ?>
        <div class="ticket-container">
            <div class="ticket-header text-center">
                <h3 style="margin: 0 0 5px 0; font-size: 15px;">SMASHIT BURGERS</h3>
                <p class="mb-1">De: Thiago Mendez</p>
                <p class="mb-1">Av. Mariscal López 123, Asunción</p>
                <p class="mb-1">Teléfono: 0981 999 888</p>
                <p class="mb-1" style="font-size: 10px;">RUC: 80012345-0</p>
            </div>
            
            <div class="mb-2">
                <div><strong>TIMBRADO:</strong> <?php echo htmlspecialchars($invoice['timbrado_no']); ?></div>
                <div><strong>VENCE:</strong> <?php echo date('d/m/Y', strtotime($invoice['timbrado_expiry'])); ?></div>
                <div><strong>COMPROBANTE:</strong> TICKET FACTURA</div>
                <div><strong>Nº FACTURA:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></div>
                <div><strong>FECHA:</strong> <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></div>
                <div><strong>CAJERO:</strong> Admin CRM</div>
            </div>

            <div class="ticket-divider"></div>

            <div class="mb-2">
                <div><strong>CLIENTE:</strong> <?php echo htmlspecialchars($invoice['customer_name']); ?></div>
                <div><strong>RUC / CI:</strong> <?php echo htmlspecialchars($invoice['customer_ruc'] ?: $invoice['customer_phone']); ?></div>
                <div><strong>CONDICIÓN:</strong> <?php echo htmlspecialchars($invoice['condition']); ?></div>
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
                    <td>TOTAL GENERAL:</td>
                    <td class="text-end"><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> Gs.</td>
                </tr>
                <tr style="font-size: 9px; font-weight: normal; color: #555;">
                    <td>LIQ. IVA 10%:</td>
                    <td class="text-end"><?php echo number_format($invoice['total_iva10'], 0, ',', '.'); ?> Gs.</td>
                </tr>
                <tr style="font-size: 9px; font-weight: normal; color: #555;">
                    <td>TOTAL IVA:</td>
                    <td class="text-end"><?php echo number_format($invoice['total_iva5'] + $invoice['total_iva10'], 0, ',', '.'); ?> Gs.</td>
                </tr>
            </table>

            <div class="ticket-divider"></div>

            <div class="text-center mb-1">
                <strong>Smashed to Perfection 🔥</strong>
            </div>
            <div class="text-center" style="font-size: 10px; margin-top: 15px;">
                ¡Gracias por su compra!
                <br>Exija su comprobante legal
            </div>
        </div>
    <?php endif; ?>

    <!-- RENDERIZAR FORMATOS A4 (Recibo, Autoimpresa, Electrónica) -->
    <?php if ($printType !== 'ticket'): ?>
        <div class="invoice-container">
            <!-- CABECERA PRINCIPAL CON NORMAS DE LA SET -->
            <table class="invoice-header-table">
                <tr>
                    <td style="width: 50%;">
                        <div class="logo-box">SMASHIT BURGERS</div>
                        <div class="fw-bold mb-1">De: Thiago Mendez</div>
                        <div>Actividad Económica: Servicios de Hamburguesería y Gastronomía</div>
                        <div>Casa Central: Av. Mariscal López 123, Asunción</div>
                        <div>Teléfono: 0981 999 888</div>
                        <div>Sucursal: <?php echo htmlspecialchars($invoice['sucursal']); ?></div>
                    </td>
                    <td style="width: 50%;">
                        <div class="meta-box">
                            <?php if ($printType === 'recibo'): ?>
                                <h4 class="text-center" style="margin: 0 0 10px 0; color: #555;">RECIBO DE DINERO</h4>
                                <div class="fw-bold" style="font-size: 14px; letter-spacing: 1px;">RUC: 80012345-0</div>
                                <div class="fw-bold text-success" style="font-size: 15px; margin-top: 5px;">Nº <?php echo htmlspecialchars($invoice['invoice_number']); ?></div>
                            <?php elseif ($printType === 'electronica'): ?>
                                <h4 class="text-center" style="margin: 0 0 10px 0; color: #008080;">FACTURA ELECTRÓNICA</h4>
                                <div class="mb-2 text-center"><span class="kude-badge">REPRESENTACIÓN GRÁFICA - KUDE</span></div>
                                <div class="small mb-1"><strong>CDC Code:</strong> 0120260810<?php echo str_pad($invoice['id'], 34, "0", STR_PAD_LEFT); ?></div>
                                <div class="small fw-bold">RUC Emisor: 80012345-0</div>
                            <?php else: ?>
                                <div class="fw-bold" style="font-size: 13px;">TIMBRADO NRO.: <?php echo htmlspecialchars($invoice['timbrado_no']); ?></div>
                                <div class="mb-1">Fecha Inicio Vigencia: 10/08/2026</div>
                                <div class="mb-2">Fecha Fin Vigencia: <?php echo date('d/m/Y', strtotime($invoice['timbrado_expiry'])); ?></div>
                                <div class="fw-bold" style="font-size: 14px; letter-spacing: 1px;">RUC: 80012345-0</div>
                                <div class="fw-bold" style="font-size: 16px; margin-top: 5px;">FACTURA</div>
                                <div class="fw-bold" style="font-size: 15px; color: #d00; margin-top: 5px;">Nº <?php echo htmlspecialchars($invoice['invoice_number']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- DATOS DEL CLIENTE -->
            <table class="customer-table">
                <tr>
                    <td style="width: 15%;" class="fw-bold">Fecha de Emisión:</td>
                    <td style="width: 45%;"><?php echo date('d/m/Y', strtotime($invoice['invoice_date'])); ?></td>
                    <td style="width: 20%;" class="fw-bold">Condición de Venta:</td>
                    <td style="width: 20%;"><?php echo htmlspecialchars($invoice['condition']); ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Nombre o Razón Social:</td>
                    <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                    <td class="fw-bold">RUC / CI:</td>
                    <td><?php echo htmlspecialchars($invoice['customer_ruc'] ?: $invoice['customer_phone']); ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Dirección:</td>
                    <td>
                        <?php 
                        $rawAddress = !empty($invoice['order_address']) ? $invoice['order_address'] : (!empty($invoice['customer_address']) ? $invoice['customer_address'] : 'Asunción, Paraguay');
                        $cleanPrintAddress = preg_replace('/\[Ubicación:\s*https?:\/\/[^\]]+\]/', '', $rawAddress);
                        echo htmlspecialchars(trim($cleanPrintAddress));
                        ?>
                    </td>
                    <td class="fw-bold">Teléfono:</td>
                    <td><?php echo htmlspecialchars($invoice['customer_phone']); ?></td>
                </tr>
            </table>

            <!-- DETALLE DE PRODUCTOS E IMPUESTOS -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 10%;">CANT.</th>
                        <th style="width: 45%;">DESCRIPCIÓN DE MERCADERÍAS O SERVICIOS</th>
                        <th class="text-end" style="width: 15%;">PRECIO UNIT.</th>
                        <th class="text-end" style="width: 10%;">EXENTAS</th>
                        <th class="text-end" style="width: 10%;">5%</th>
                        <th class="text-end" style="width: 10%;">10%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 0;
                    foreach ($items as $item): 
                        $count++;
                        $subtotal = (float)$item['subtotal'];
                        
                        $exento = 0;
                        $iva5 = 0;
                        $iva10 = 0;

                        if ($item['tax_rate'] == 10) {
                            $iva10 = $subtotal;
                        } elseif ($item['tax_rate'] == 5) {
                            $iva5 = $subtotal;
                        } else {
                            $exento = $subtotal;
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="text-end"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                            <td class="text-end"><?php echo $exento > 0 ? number_format($exento, 0, ',', '.') : '0'; ?></td>
                            <td class="text-end"><?php echo $iva5 > 0 ? number_format($iva5, 0, ',', '.') : '0'; ?></td>
                            <td class="text-end"><?php echo $iva10 > 0 ? number_format($iva10, 0, ',', '.') : '0'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Rellenar filas vacías para dar apariencia de factura preimpresa (mínimo 5 filas) -->
                    <?php for ($i = $count; $i < 5; $i++): ?>
                        <tr>
                            <td class="text-center">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-end">&nbsp;</td>
                            <td class="text-end">&nbsp;</td>
                            <td class="text-end">&nbsp;</td>
                            <td class="text-end">&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <!-- TOTALES Y LIQUIDACIÓN DE IVA -->
            <table class="summary-table">
                <tr>
                    <td colspan="3" class="fw-bold">SUBTOTALES:</td>
                    <td class="text-end" style="width: 10%;"><?php echo number_format($invoice['total_exento'], 0, ',', '.'); ?></td>
                    <td class="text-end" style="width: 10%;"><?php echo number_format($invoice['total_iva5'] * 21, 0, ',', '.'); ?></td>
                    <td class="text-end" style="width: 10%;"><?php echo number_format($invoice['total_iva10'] * 11, 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td colspan="5" class="fw-bold">TOTAL GENERAL (en Letras): <span class="fw-normal" style="margin-left: 10px; font-style: italic;"><?php echo numeroAPalabras($invoice['total_amount']); ?> GUARANÍES.-</span></td>
                    <td class="text-end fw-bold" style="font-size: 12px;"><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> Gs.</td>
                </tr>
                <tr class="fw-bold">
                    <td style="width: 33%;">LIQUIDACIÓN DEL IVA:</td>
                    <td style="width: 33%;">IVA 5%: <span class="fw-normal"><?php echo number_format($invoice['total_iva5'], 0, ',', '.'); ?> Gs.</span></td>
                    <td style="width: 34%;" colspan="4">IVA 10%: <span class="fw-normal"><?php echo number_format($invoice['total_iva10'], 0, ',', '.'); ?> Gs.</span></td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="6">TOTAL IVA: <span class="fw-normal"><?php echo number_format($invoice['total_iva5'] + $invoice['total_iva10'], 0, ',', '.'); ?> Gs.</span></td>
                </tr>
            </table>
            
            <?php if ($printType === 'electronica'): ?>
                <div class="mt-4 p-3 text-center text-muted" style="border: 1px dashed #ccc; border-radius: 5px; font-size: 11px; background-color: #fbfbfb;">
                    <div class="fw-bold mb-1" style="color:#008080;">ESTA ES UNA REPRESENTACIÓN GRÁFICA DE UN COMPROBANTE ELECTRÓNICO (KUDE)</div>
                    Consulte la validez de esta Factura Electrónica ingresando al portal del Ministerio de Hacienda con el código CDC proveído en la cabecera.
                </div>
            <?php endif; ?>
            
            <?php if ($invoice['status'] === 'anulada'): ?>
                <div style="border: 4px solid red; color: red; font-size: 32px; font-weight: bold; text-align: center; padding: 10px; margin-top: 15px; transform: rotate(-5deg); opacity: 0.8;">
                    DOCUMENTO ANULADO
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
