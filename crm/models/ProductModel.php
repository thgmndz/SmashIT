<?php
class ProductModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY c.sort_order ASC, p.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        return $product ? $product : null;
    }

    public function updatePriceAvailabilityAndBadge(int $id, float $price, bool $isAvailable, ?string $badge = null, ?string $imageUrl = null): bool {
        if ($imageUrl !== null) {
            $stmt = $this->db->prepare("UPDATE products SET base_price = :price, is_available = :is_available, badge = :badge, image_url = :image_url WHERE id = :id");
            return $stmt->execute([
                ':id' => $id,
                ':price' => $price,
                ':is_available' => $isAvailable ? 1 : 0,
                ':badge' => !empty($badge) ? $badge : null,
                ':image_url' => $imageUrl
            ]);
        } else {
            $stmt = $this->db->prepare("UPDATE products SET base_price = :price, is_available = :is_available, badge = :badge WHERE id = :id");
            return $stmt->execute([
                ':id' => $id,
                ':price' => $price,
                ':is_available' => $isAvailable ? 1 : 0,
                ':badge' => !empty($badge) ? $badge : null
            ]);
        }
    }

    public function getCategories(): array {
        return $this->db->query("SELECT id, name FROM categories ORDER BY sort_order ASC")->fetchAll();
    }

    public function getTotalCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    }

    public function insert(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO products (category_id, name, slug, description, base_price, image_url, badge, is_available, is_featured) 
            VALUES (:category_id, :name, :slug, :description, :base_price, :image_url, :badge, :is_available, :is_featured)
        ");
        return $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'],
            ':base_price' => $data['base_price'],
            ':image_url' => $data['image_url'] ?? '',
            ':badge' => $data['badge'] ?? null,
            ':is_available' => $data['is_available'] ? 1 : 0,
            ':is_featured' => $data['is_featured'] ? 1 : 0
        ]);
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}
