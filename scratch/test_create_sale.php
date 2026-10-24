<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/Order.php';

use SmashIT\Models\Order;

$res = Order::create([
    'customer_name' => 'Cliente Test CRUD',
    'customer_phone' => '0981999999',
    'customer_email' => 'test@smashit.com',
    'delivery_type' => 'pickup',
    'delivery_address' => '',
    'payment_method' => 'Efectivo',
    'subtotal' => 45000,
    'delivery_fee' => 0,
    'total_amount' => 45000,
    'notes' => 'Prueba de registro de venta desde CRUD',
    'items' => [
        [
            'id' => 1,
            'name' => 'Smash Doble Queso',
            'price' => 45000,
            'quantity' => 1,
            'subtotal' => 45000,
            'options_summary' => ''
        ]
    ]
]);

echo "Venta creada resultado: " . json_encode($res) . "\n";
