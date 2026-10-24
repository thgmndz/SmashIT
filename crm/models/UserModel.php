<?php
class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = CRMDatabase::getConnection();
    }

    public function authenticate(string $username, string $password): ?array {
        $hashedPassword = md5($password);
        $stmt = $this->db->prepare("SELECT id, username, name, role FROM users WHERE username = :username AND password = :password");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword
        ]);
        
        $user = $stmt->fetch();
        return $user ? $user : null;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, username, name, role FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ? $user : null;
    }
}
