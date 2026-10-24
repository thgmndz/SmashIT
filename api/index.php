<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/Product.php';
require_once __DIR__ . '/../src/Models/Order.php';
require_once __DIR__ . '/../src/Controllers/ProductController.php';
require_once __DIR__ . '/../src/Controllers/OrderController.php';

use SmashIT\Controllers\ProductController;
use SmashIT\Controllers\OrderController;

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'products';

// Para llamadas JSON POST
$jsonInput = json_decode(file_get_contents('php://input'), true) ?: [];
$data = array_merge($_POST, $jsonInput);

switch ($action) {
    case 'products':
        ProductController::index();
        break;

    case 'product_detail':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        ProductController::detail($id);
        break;

    case 'checkout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            OrderController::store();
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
        }
        break;

    case 'order_status':
        $code = $_GET['code'] ?? '';
        OrderController::status($code);
        break;

    case 'check_qr_token':
        $token = $_GET['token'] ?? '';
        OrderController::checkQrToken($token);
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint no válido']);
        break;
}
