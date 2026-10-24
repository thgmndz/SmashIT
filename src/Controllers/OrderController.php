<?php

namespace SmashIT\Controllers;

use SmashIT\Models\Order;

class OrderController {
    public static function store(): void {
        header('Content-Type: application/json');
        
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data || empty($data['items']) || empty($data['customer_name']) || empty($data['customer_phone'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Faltan datos obligatorios para procesar el pedido.'
            ]);
            return;
        }

        $result = Order::create($data);

        if ($result['success']) {
            echo json_encode([
                'status' => 'success',
                'order_code' => $result['order_code'],
                'message' => '¡Pedido creado con éxito!'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al guardar el pedido: ' . $result['message']
            ]);
        }
    }

    public static function status(string $code): void {
        header('Content-Type: application/json');

        $order = Order::getByCode($code);

        if (!$order) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Pedido no encontrado.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'order' => $order
        ]);
    }

    public static function checkQrToken(string $token): void {
        header('Content-Type: application/json');

        if (empty($token)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token no proporcionado.'
            ]);
            return;
        }

        $orderCode = Order::getByQrToken($token);

        if ($orderCode) {
            echo json_encode([
                'status' => 'success',
                'order_code' => $orderCode
            ]);
        } else {
            echo json_encode([
                'status' => 'pending',
                'message' => 'El pedido aún no ha sido confirmado.'
            ]);
        }
    }
}
