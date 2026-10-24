<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/Order.php';

use SmashIT\Models\Order;

// Fetch completed orders
$orders = Order::getRecentOrders(50);
$completed = array_values(array_filter($orders, fn($o) => $o['status'] === 'completed'));

echo "Pedidos completados iniciales: " . count($completed) . "\n";
if (!empty($completed)) {
    $firstCode = $completed[0]['order_code'];
    echo "Probando marcar como entregado pedido: #{$firstCode}...\n";
    $ok = Order::markDelivered($firstCode);
    echo "Resultado marcar entregado: " . ($ok ? "OK" : "ERROR") . "\n";

    // Re-check completed & not delivered
    $ordersAfter = Order::getRecentOrders(50);
    $activeNotifOrders = array_values(array_filter($ordersAfter, fn($o) => $o['status'] === 'completed' && empty($o['is_delivered'])));
    echo "Notificaciones activas de pedidos tras marcar entregado: " . count($activeNotifOrders) . "\n";
    echo "Total pedidos en historial de ventas: " . count($ordersAfter) . "\n";
}
