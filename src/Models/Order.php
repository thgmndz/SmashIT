<?php

namespace SmashIT\Models;

use SmashIT\Database;
use PDO;

class Order {
    public static function generateOrderCode(): string {
        return 'SMASH-' . strtoupper(substr(uniqid(), -6));
    }

    public static function create(array $data): array {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $orderCode = self::generateOrderCode();
            $stmt = $db->prepare("
                INSERT INTO orders (
                    order_code, customer_name, customer_email, customer_phone, 
                    delivery_type, delivery_address, payment_method, 
                    subtotal, delivery_fee, total_amount, status, notes
                ) VALUES (
                    :order_code, :customer_name, :customer_email, :customer_phone, 
                    :delivery_type, :delivery_address, :payment_method, 
                    :subtotal, :delivery_fee, :total_amount, 'pending', :notes
                )
            ");

            $stmt->execute([
                'order_code' => $orderCode,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'delivery_type' => $data['delivery_type'] ?? 'delivery',
                'delivery_address' => $data['delivery_address'] ?? '',
                'payment_method' => $data['payment_method'] ?? 'Efectivo',
                'subtotal' => $data['subtotal'],
                'delivery_fee' => $data['delivery_fee'],
                'total_amount' => $data['total_amount'],
                'notes' => $data['notes'] ?? ''
            ]);

            $orderId = $db->lastInsertId();

            $itemStmt = $db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, product_name, unit_price, quantity, subtotal, options_summary
                ) VALUES (
                    :order_id, :product_id, :product_name, :unit_price, :quantity, :subtotal, :options_summary
                )
            ");

            foreach ($data['items'] as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'options_summary' => $item['options_summary'] ?? ''
                ]);
            }

            $db->commit();

            return [
                'success' => true,
                'order_code' => $orderCode,
                'order_id' => $orderId
            ];

        } catch (\Exception $e) {
            $db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getByCode(string $code): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_code = :code");
        $stmt->execute(['code' => trim($code)]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $itemsStmt->execute(['order_id' => $order['id']]);
        $order['items'] = $itemsStmt->fetchAll();

        return $order;
    }

    public static function getByQrToken(string $token): ?string {
        $db = Database::getConnection();
        $search = '%[QR_TOKEN:' . $token . ']%';
        $stmt = $db->prepare("SELECT order_code FROM orders WHERE notes LIKE :search LIMIT 1");
        $stmt->execute(['search' => $search]);
        $row = $stmt->fetch();
        return $row ? $row['order_code'] : null;
    }
}
