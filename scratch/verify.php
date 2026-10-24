<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/Product.php';

$products = SmashIT\Models\Product::getProducts();
foreach ($products as $p) {
    echo $p['id'] . " | [" . $p['category_name'] . "] " . $p['name'] . " -> " . number_format($p['base_price'], 0, ',', '.') . " Gs.\n";
}
