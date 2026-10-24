<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/DeliveryDriverModel.php';

AuthController::checkAuth();

$driverModel = new DeliveryDriverModel();
$reportData = $driverModel->getDriverMonthlyReport();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Mensual de Repartos por Chofer</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #eaeaea;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        @media print {
            .btn-print {
                display: none;
            }
        }
        .btn-print {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ffb142;
            color: #000;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div style="text-align: right;">
        <button class="btn-print" onclick="window.print()">Imprimir Reporte 🖨️</button>
    </div>

    <div class="header">
        <h1>SmashIT Burgers</h1>
        <p>Reporte Mensual de Repartos y Cobros por Chofer de Delivery</p>
        <p>Mes de Facturación: <?php echo date('F Y'); ?></p>
        <p>Fecha de Generación: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Chofer</th>
                <th>Teléfono</th>
                <th>Vehículo</th>
                <th class="text-center">Cant. Repartos Entregados</th>
                <th class="text-end">Total Recaudado en Pedidos</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grandTotal = 0;
            $grandQty = 0;
            if (empty($reportData)): 
            ?>
                <tr>
                    <td colspan="5" class="text-center">No se encontraron repartos registrados este mes.</td>
                </tr>
            <?php 
            else: 
                foreach ($reportData as $row): 
                    $grandTotal += $row['total_amount'];
                    $grandQty += $row['total_deliveries'];
            ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['driver_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['driver_phone'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['driver_vehicle'] ?: 'N/A'); ?></td>
                    <td class="text-center"><?php echo $row['total_deliveries']; ?></td>
                    <td class="text-end"><?php echo number_format($row['total_amount'], 0, ',', '.'); ?> Gs.</td>
                </tr>
            <?php 
                endforeach; 
            ?>
                <tr class="total-row">
                    <td colspan="3">TOTAL GENERAL</td>
                    <td class="text-center"><?php echo $grandQty; ?></td>
                    <td class="text-end"><?php echo number_format($grandTotal, 0, ',', '.'); ?> Gs.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>SmashIT CRM - Sistema de Control y Reportes Interno</p>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
