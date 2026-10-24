<?php
require_once __DIR__ . '/../crm/config/db.php';

try {
    $db = CRMDatabase::getConnection();
    
    // Check customer with id 4
    $stmt = $db->prepare("SELECT * FROM customers WHERE id = 4");
    $stmt->execute();
    $cust = $stmt->fetch();
    echo "CUSTOMER 4:\n";
    print_r($cust);
    
    // Test getUnpaidInvoices SQL output
    $stmt2 = $db->query("
        SELECT i.*, c.name AS customer_name, c.phone AS customer_phone, c.ruc_ci AS customer_ruc
        FROM invoices i
        JOIN customers c ON i.customer_id = c.id
        WHERE i.status = 'pendiente'
    ");
    $res = $stmt2->fetchAll();
    echo "\nUNPAID INVOICES QUERY RESULT:\n";
    print_r($res);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
