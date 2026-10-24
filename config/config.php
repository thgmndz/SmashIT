<?php
// Configuración general del sistema SmashIT Burgers

define('APP_NAME', 'SmashIT Burgers');
define('APP_TAGLINE', 'Smashed to Perfection');
define('CURRENCY_SYMBOL', 'Gs.');
define('DELIVERY_FEE', 10000.00);

// Base de datos (Configuración MySQL/MariaDB para XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123');
define('DB_NAME', 'smashit_db');

// Fallback SQLite si MySQL no estuviera disponible/configurado
define('SQLITE_PATH', __DIR__ . '/../database/smashit.sqlite');

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');
