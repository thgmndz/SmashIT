<?php

namespace SmashIT\Models;

use SmashIT\Database;
use PDO;

class Product {
    public static function getAllCategories(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM categories WHERE active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public static function getProducts(?int $categoryId = null, ?string $search = null): array {
        $db = Database::getConnection();
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.is_available = 1";
        
        $params = [];

        if ($categoryId !== null) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($search !== null && trim($search) !== '') {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY p.is_featured DESC, p.id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = :id AND p.is_available = 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            return null;
        }

        // Obtener grupos de opciones vinculados
        $product['option_groups'] = self::getProductOptions($id);

        return $product;
    }

    public static function getProductOptions(int $productId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT og.* 
            FROM option_groups og
            JOIN product_options po ON og.id = po.option_group_id
            WHERE po.product_id = :product_id
            ORDER BY og.id ASC
        ");
        $stmt->execute(['product_id' => $productId]);
        $groups = $stmt->fetchAll();

        foreach ($groups as &$group) {
            $itemStmt = $db->prepare("
                SELECT * FROM option_items 
                WHERE option_group_id = :group_id 
                ORDER BY id ASC
            ");
            $itemStmt->execute(['group_id' => $group['id']]);
            $group['items'] = $itemStmt->fetchAll();
        }

        return $groups;
    }
}
