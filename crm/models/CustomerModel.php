<?php
class CustomerModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM customers ORDER BY total_orders DESC, created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $customer = $stmt->fetch();
        return $customer ? $customer : null;
    }

    public function getByPhone(string $phone): ?array {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE phone = :phone");
        $stmt->execute([':phone' => $phone]);
        $customer = $stmt->fetch();
        return $customer ? $customer : null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO customers (name, phone, ruc_ci, email, address, total_orders) VALUES (:name, :phone, :ruc_ci, :email, :address, :total_orders)");
        return $stmt->execute([
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':ruc_ci' => $data['ruc_ci'] ?? null,
            ':email' => $data['email'] ?? null,
            ':address' => $data['address'] ?? null,
            ':total_orders' => (int)($data['total_orders'] ?? 0)
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE customers SET name = :name, phone = :phone, ruc_ci = :ruc_ci, email = :email, address = :address, total_orders = :total_orders WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':ruc_ci' => $data['ruc_ci'] ?? null,
            ':email' => $data['email'] ?? null,
            ':address' => $data['address'] ?? null,
            ':total_orders' => (int)($data['total_orders'] ?? 0)
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getTotalCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    }
}
