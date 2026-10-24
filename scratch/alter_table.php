<?php
require_once __DIR__ . '/../crm/config/db.php';
try {
    $db = CRMDatabase::getConnection();
    $db->exec("ALTER TABLE customers ADD COLUMN ruc_ci VARCHAR(30) NULL AFTER phone");
    echo "[+] Tabla customers alterada con éxito para incluir 'ruc_ci'.\n";
} catch (Exception $e) {
    echo "[-] Error: " . $e->getMessage() . "\n";
}
