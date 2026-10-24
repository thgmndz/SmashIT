<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/AdminModel.php';
require_once __DIR__ . '/../src/Models/Order.php';
require_once __DIR__ . '/../src/Models/Product.php';
require_once __DIR__ . '/../src/Controllers/OrderController.php';

use SmashIT\Models\AdminModel;
use SmashIT\Models\Order;
use SmashIT\Models\Product;

// Get products to order
$products = Product::getProducts();
if (empty($products)) {
    die("No hay productos disponibles.\n");
}

$p1 = $products[0];
$p2 = isset($products[1]) ? $products[1] : $products[0];

$payload = [
    'table_number' => 7,
    'customer_name' => 'Mesa #7 (Consumo en Local)',
    'customer_phone' => 'Mesa #7',
    'customer_email' => 'local@smashit.com',
    'delivery_type' => 'pickup',
    'delivery_address' => 'Mesa #7 - Salón',
    'payment_method' => 'Efectivo',
    'subtotal' => (float)$p1['base_price'] + (float)$p2['base_price'],
    'delivery_fee' => 0,
    'total_amount' => (float)$p1['base_price'] + (float)$p2['base_price'],
    'notes' => 'Mesa #7: Extra salsa smash, sin pepinillo',
    'items' => [
        [
            'id' => $p1['id'],
            'name' => $p1['name'],
            'price' => (float)$p1['base_price'],
            'quantity' => 1,
            'subtotal' => (float)$p1['base_price'],
            'options_summary' => ''
        ],
        [
            'id' => $p2['id'],
            'name' => $p2['name'],
            'price' => (float)$p2['base_price'],
            'quantity' => 1,
            'subtotal' => (float)$p2['base_price'],
            'options_summary' => ''
        ]
    ]
];

echo "=== PRUEBA DE REGISTRO DE COMANDA PARA MESA #7 ===\n";
$res = Order::create($payload);
echo "Order create result: " . ($res['success'] ? "OK" : "ERROR: " . $res['message']) . "\n";

if ($res['success']) {
    $orderCode = $res['order_code'];
    echo "Código de comanda generado: {$orderCode}\n";

    // Actualizar estado de mesa 7
    $updatedTable = AdminModel::updateTableStatus(7, 'ocupado', $orderCode);
    echo "Mesa #7 actualizada a OCUPADO: " . ($updatedTable ? "OK" : "ERROR") . "\n";

    // Verificar en lista de mesas
    $tables = AdminModel::getRestaurantTables();
    $mesa7 = array_values(array_filter($tables, fn($t) => (int)$t['table_number'] === 7))[0];
    echo "Mesa #7 - Estado actual: {$mesa7['status']} | Comanda: {$mesa7['current_order_code']}\n";

    // Verificar pedido en órdenes recientes
    $savedOrder = Order::getByCode($orderCode);
    echo "Pedido recuperado de la BD: Cliente: {$savedOrder['customer_name']} | Ítems: " . count($savedOrder['items']) . "\n";
}
