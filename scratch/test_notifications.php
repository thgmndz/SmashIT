<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/AdminModel.php';
require_once __DIR__ . '/../src/Models/Order.php';

use SmashIT\Models\AdminModel;
use SmashIT\Models\Order;

$ingredients = AdminModel::getIngredients();
$orders = Order::getRecentOrders(50);

$lowStock = array_values(array_filter($ingredients, fn($ing) => (float)$ing['current_stock'] <= 3));
$completed = array_values(array_filter($orders, fn($o) => $o['status'] === 'completed'));

echo "=== TEST NOTIFICACIONES ===\n";
echo "Insumos con Bajo Stock (<=3): " . count($lowStock) . "\n";
foreach ($lowStock as $ing) {
    echo " - " . $ing['name'] . " (" . $ing['code'] . "): " . $ing['current_stock'] . " " . $ing['unit'] . "\n";
}

echo "Pedidos Completados en Cocina: " . count($completed) . "\n";
foreach ($completed as $c) {
    $isMesa = (strpos($c['customer_name'], 'Mesa #') !== false || $c['delivery_type'] === 'mesa');
    $tipo = $isMesa ? "Consumo en el Local (" . $c['customer_name'] . ")" : ($c['delivery_type'] === 'delivery' ? "A Domicilio" : "Para Retirar");
    echo " - Pedido #" . $c['order_code'] . " | Tipo: " . $tipo . " | Cliente: " . $c['customer_name'] . " | Ítems: " . count($c['items']) . "\n";
}
