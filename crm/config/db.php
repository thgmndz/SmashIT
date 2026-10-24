<?php
// Configuración de la base de datos para el CRM de SmashIT Burgers
define('CRM_DB_HOST', '127.0.0.1');
define('CRM_DB_PORT', '3306');
define('CRM_DB_USER', 'root');
define('CRM_DB_PASS', '123');
define('CRM_DB_NAME', 'smashit_db');

class CRMDatabase {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . CRM_DB_HOST . ";port=" . CRM_DB_PORT . ";dbname=" . CRM_DB_NAME . ";charset=utf8mb4";
                self::$instance = new PDO($dsn, CRM_DB_USER, CRM_DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                
                try { self::$instance->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_name VARCHAR(100) NULL"); } catch (Exception $e) {}
                try { self::$instance->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_ci VARCHAR(30) NULL"); } catch (Exception $e) {}
                try { self::$instance->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_datetime DATETIME NULL"); } catch (Exception $e) {}
            } catch (PDOException $e) {
                try {
                    $dsnNoDb = "mysql:host=" . CRM_DB_HOST . ";port=" . CRM_DB_PORT . ";charset=utf8mb4";
                    $pdo = new PDO($dsnNoDb, CRM_DB_USER, CRM_DB_PASS);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . CRM_DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    
                    $dsn = "mysql:host=" . CRM_DB_HOST . ";port=" . CRM_DB_PORT . ";dbname=" . CRM_DB_NAME . ";charset=utf8mb4";
                    self::$instance = new PDO($dsn, CRM_DB_USER, CRM_DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);

                    try { self::$instance->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_name VARCHAR(100) NULL"); } catch (Exception $e) {}
                    try { self::$instance->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_ci VARCHAR(30) NULL"); } catch (Exception $e) {}
                    try { self::$instance->exec("ALTER TABLE restaurant_tables ADD COLUMN reservation_datetime DATETIME NULL"); } catch (Exception $e) {}
                } catch (PDOException $ex) {
                    die("Error de conexión a la base de datos: " . $ex->getMessage());
                }
            }
        }
        return self::$instance;
    }
}
