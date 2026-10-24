<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';

$pdo = SmashIT\Database::getConnection();

$tables = ['categories', 'products', 'suppliers', 'ingredients', 'product_recipes', 'employees', 'customers', 'purchases', 'purchase_items', 'cash_registers', 'orders', 'order_items'];

echo "=== VERIFICACIÓN DE TABLAS ERP SMASHIT ===\n";
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo str_pad($table, 20) . " : " . $count . " registros\n";
}
