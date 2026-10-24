<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/AdminModel.php';

use SmashIT\Models\AdminModel;

$tables = AdminModel::getRestaurantTables();
echo "Mesas obtenidas: " . count($tables) . "\n";
foreach ($tables as $t) {
    echo "Mesa #" . $t['table_number'] . " | Capacidad: " . $t['capacity'] . " | Estado: " . $t['status'] . "\n";
}

// Test status update
$ok = AdminModel::updateTableStatus(2, 'ocupado', 'SMASH-TEST-MESA2');
echo "Actualizar Mesa 2 a ocupado: " . ($ok ? "OK" : "ERROR") . "\n";
