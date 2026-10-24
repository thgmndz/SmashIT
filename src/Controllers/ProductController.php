<?php

namespace SmashIT\Controllers;

use SmashIT\Models\Product;

class ProductController {
    public static function index(): void {
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $search = $_GET['search'] ?? null;

        $categories = Product::getAllCategories();
        $products = Product::getProducts($categoryId, $search);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'categories' => $categories,
            'products' => $products
        ]);
    }

    public static function detail(int $id): void {
        $product = Product::getById($id);

        header('Content-Type: application/json');
        if (!$product) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'product' => $product
        ]);
    }
}
