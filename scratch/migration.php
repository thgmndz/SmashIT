<?php
require_once __DIR__ . '/../crm/config/db.php';

try {
    $db = CRMDatabase::getConnection();
    
    // Check if cash_register_id exists in invoices
    $q = $db->query("SHOW COLUMNS FROM invoices LIKE 'cash_register_id'");
    $exists = $q->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE invoices ADD COLUMN cash_register_id INT NULL DEFAULT NULL AFTER order_id");
        $db->exec("ALTER TABLE invoices ADD CONSTRAINT fk_invoices_cash_register FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE SET NULL");
        echo "Migration completed: cash_register_id column added to invoices table.\n";
    } else {
        echo "Column cash_register_id already exists in invoices.\n";
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
