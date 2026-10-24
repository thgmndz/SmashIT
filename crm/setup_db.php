<?php
require_once __DIR__ . '/config/db.php';

try {
    $db = CRMDatabase::getConnection();

    // 1. Crear tabla de usuarios para el CRM si no existe
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(32) NOT NULL,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(20) DEFAULT 'admin',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "[+] Tabla 'users' creada o ya existente.\n";

    // 2. Verificar e importar el esquema completo de la tienda web si no existe la tabla 'products'
    $stmtProducts = $db->query("SHOW TABLES LIKE 'products'");
    if ($stmtProducts->rowCount() === 0) {
        echo "[*] Importando esquema completo de la tienda web (schema.sql)...\n";
        $schemaPath = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaPath)) {
            $schemaSql = file_get_contents($schemaPath);
            $db->exec($schemaSql);
            echo "[+] Esquema importado con éxito.\n";
        } else {
            echo "[-] Error: No se encontró database/schema.sql\n";
        }

        echo "[*] Insertando datos de prueba (seed.sql)...\n";
        $seedPath = __DIR__ . '/../database/seed.sql';
        if (file_exists($seedPath)) {
            $seedSql = file_get_contents($seedPath);
            try {
                $db->exec($seedSql);
                echo "[+] Datos de prueba sembrados con éxito.\n";
            } catch (Exception $seedEx) {
                echo "[!] Advertencia al sembrar datos: " . $seedEx->getMessage() . "\n";
            }
        }
    } else {
        echo "[*] Las tablas de la tienda web ya existen.\n";
    }

    // 2.5. Asegurar la creación de restaurant_tables en MySQL
    $stmtTables = $db->query("SHOW TABLES LIKE 'restaurant_tables'");
    if ($stmtTables->rowCount() === 0) {
        echo "[*] Creando tabla 'restaurant_tables'...\n";
        $db->exec("
            CREATE TABLE IF NOT EXISTS restaurant_tables (
                id INT AUTO_INCREMENT PRIMARY KEY,
                table_number INT NOT NULL UNIQUE,
                status VARCHAR(20) DEFAULT 'libre',
                capacity INT DEFAULT 4,
                current_order_code VARCHAR(30) NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Cargar mesas iniciales
        $stmtInsert = $db->prepare("INSERT INTO restaurant_tables (table_number, status, capacity) VALUES (:num, :status, :cap)");
        $initialTables = [
            ['num' => 1, 'status' => 'ocupado', 'cap' => 2],
            ['num' => 2, 'status' => 'libre', 'cap' => 4],
            ['num' => 3, 'status' => 'reservado', 'cap' => 4],
            ['num' => 4, 'status' => 'libre', 'cap' => 2],
            ['num' => 5, 'status' => 'libre', 'cap' => 6],
            ['num' => 6, 'status' => 'libre', 'cap' => 4],
            ['num' => 7, 'status' => 'reservado', 'cap' => 8],
            ['num' => 8, 'status' => 'libre', 'cap' => 4],
            ['num' => 9, 'status' => 'libre', 'cap' => 2],
            ['num' => 10, 'status' => 'libre', 'cap' => 4]
        ];
        foreach ($initialTables as $t) {
            $stmtInsert->execute($t);
        }
        echo "[+] Tabla 'restaurant_tables' y datos iniciales creados.\n";
    } else {
        echo "[*] La tabla 'restaurant_tables' ya existe.\n";
    }

    // 3. Insertar usuario administrador por defecto si no existe
    $username = 'admin';
    // md5('123456') = e10adc3949ba59abbe56e057f20f883e
    $password = md5('123456');
    $name = 'Administrador CRM';
    $role = 'admin';

    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetchColumn() == 0) {
        $insert = $db->prepare("INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, :role)");
        $insert->execute([
            ':username' => $username,
            ':password' => $password,
            ':name' => $name,
            ':role' => $role
        ]);
        echo "[+] Usuario administrador por defecto creado ('admin' / '123456').\n";
    } else {
        echo "[*] El usuario 'admin' ya existe en la base de datos.\n";
    }

    echo "[+] Configuración de base de datos CRM completada con éxito.\n";

} catch (Exception $e) {
    echo "[-] Error durante la configuración: " . $e->getMessage() . "\n";
}
