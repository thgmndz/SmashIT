<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/AdminModel.php';

use SmashIT\Models\AdminModel;

// Probar guardar insumo
$testIng = [
    'code' => 'INS-TEST-99',
    'name' => 'Queso Gouda Especial',
    'unit' => 'Kg',
    'current_stock' => 50,
    'min_stock' => 10,
    'unit_cost' => 35000
];
$ok = AdminModel::saveIngredient($testIng);
echo "Insumo creado: " . ($ok ? "OK" : "ERROR") . "\n";

// Probar obtener insumos
$ingredients = AdminModel::getIngredients();
echo "Total Insumos: " . count($ingredients) . "\n";
