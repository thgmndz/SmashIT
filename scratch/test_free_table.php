<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/AdminModel.php';
require_once __DIR__ . '/../src/Models/Order.php';

use SmashIT\Models\AdminModel;
use SmashIT\Models\Order;

// Check Mesa #7
$tables = AdminModel::getRestaurantTables();
$mesa7 = array_values(array_filter($tables, fn($t) => (int)$t['table_number'] === 7))[0];
echo "=== ESTADO DE MESA #7 ANTES DE LIBERAR ===\n";
echo "Estado: {$mesa7['status']} | Comanda: {$mesa7['current_order_code']}\n";

$code = $mesa7['current_order_code'];
if ($code) {
    echo "Marcando pedido #{$code} como entregado...\n";
    Order::markDelivered($code);
}

echo "Actualizando Mesa #7 a LIBRE...\n";
AdminModel::updateTableStatus(7, 'libre', null);

$tablesAfter = AdminModel::getRestaurantTables();
$mesa7After = array_values(array_filter($tablesAfter, fn($t) => (int)$t['table_number'] === 7))[0];
echo "=== ESTADO DE MESA #7 TRAS LIBERAR ===\n";
echo "Estado: {$mesa7After['status']} | Comanda: " . ($mesa7After['current_order_code'] ?? 'NULL') . "\n";
