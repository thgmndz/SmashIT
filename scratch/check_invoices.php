<?php
require_once __DIR__ . '/../crm/config/db.php';

try {
    $db = CRMDatabase::getConnection();
    
    // Check all invoices
    $stmt = $db->query("SELECT * FROM invoices");
    $invoices = $stmt->fetchAll();
    echo "INVOICES:\n";
    print_r($invoices);
    
    // Check all cash registers
    $stmt2 = $db->query("SELECT * FROM cash_registers");
    $registers = $stmt2->fetchAll();
    echo "\nCASH REGISTERS:\n";
    print_r($registers);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
