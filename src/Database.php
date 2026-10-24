<?php

namespace SmashIT;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $useSqlite = false;

        try {
            // Intentar conexión MySQL primero
            $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Verificar si existe la base de datos MySQL, de lo contrario crearla
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo->exec("USE `" . DB_NAME . "`;");

            self::$instance = $pdo;
            self::checkAndMigrateMySQL($pdo);

        } catch (PDOException $e) {
            // Si MySQL falla (ej. sin servicio o sin credenciales), usar SQLite
            $useSqlite = true;
        }

        if ($useSqlite) {
            try {
                $dir = dirname(SQLITE_PATH);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $pdo = new PDO("sqlite:" . SQLITE_PATH, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                self::$instance = $pdo;
                self::checkAndMigrateSQLite($pdo);
            } catch (PDOException $e) {
                die("Error crítico de base de datos: " . $e->getMessage());
            }
        }

        if (self::$instance !== null) {
            self::ensureRestaurantTablesExist(self::$instance);
        }

        return self::$instance;
    }

    private static function ensureRestaurantTablesExist(PDO $pdo): void {
        try {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS restaurant_tables (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        table_number INTEGER NOT NULL UNIQUE,
                        status TEXT DEFAULT 'libre',
                        capacity INTEGER DEFAULT 4,
                        current_order_code TEXT NULL,
                        reservation_name TEXT NULL,
                        reservation_ci TEXT NULL,
                        reservation_datetime TEXT NULL,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS restaurant_tables (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        table_number INT NOT NULL UNIQUE,
                        status VARCHAR(20) DEFAULT 'libre',
                        capacity INT DEFAULT 4,
                        current_order_code VARCHAR(30) NULL,
                        reservation_name VARCHAR(100) NULL,
                        reservation_ci VARCHAR(30) NULL,
                        reservation_datetime DATETIME NULL,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            }
            // Ejecutar alters por si la tabla ya existía
            try { $pdo->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_name VARCHAR(100) NULL"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_ci VARCHAR(30) NULL"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_datetime DATETIME NULL"); } catch (Exception $e) {}

            $count = (int)$pdo->query("SELECT COUNT(*) FROM restaurant_tables")->fetchColumn();
            if ($count === 0) {
                $stmt = $pdo->prepare("INSERT INTO restaurant_tables (table_number, status, capacity) VALUES (:num, :status, :cap)");
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
                    $stmt->execute($t);
                }
            }

            try {
                $pdo->exec("ALTER TABLE orders ADD COLUMN is_delivered INT DEFAULT 0");
            } catch (\Exception $e) {
                // Column exists
            }
        } catch (\PDOException $e) {
            error_log("Error al verificar tabla restaurant_tables: " . $e->getMessage());
        }
    }

    private static function checkAndMigrateMySQL(PDO $pdo): void {
        $result = $pdo->query("SHOW TABLES LIKE 'products'");
        if ($result->rowCount() === 0) {
            $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
            $seed = file_get_contents(__DIR__ . '/../database/seed.sql');
            
            $pdo->exec($schema);
            $pdo->exec($seed);
        }
    }

    private static function checkAndMigrateSQLite(PDO $pdo): void {
        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
        if (!$result->fetch()) {
            $schemaRaw = file_get_contents(__DIR__ . '/../database/schema.sql');
            
            // Adaptar sintaxis MySQL de schema.sql a SQLite
            $sqliteSchema = preg_replace('/ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;/i', ';', $schemaRaw);
            $sqliteSchema = preg_replace('/INT AUTO_INCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sqliteSchema);
            $sqliteSchema = preg_replace('/TINYINT\(1\)/i', 'INTEGER', $sqliteSchema);
            $sqliteSchema = preg_replace('/ENUM\([^)]+\)/i', 'TEXT', $sqliteSchema);
            $sqliteSchema = preg_replace('/VARCHAR\(\d+\)/i', 'TEXT', $sqliteSchema);
            $sqliteSchema = preg_replace('/DECIMAL\(\d+,\s*\d+\)/i', 'REAL', $sqliteSchema);

            $pdo->exec($sqliteSchema);

            // Cargar Semilla
            $seed = file_get_contents(__DIR__ . '/../database/seed.sql');
            $pdo->exec($seed);
        }
    }
}
