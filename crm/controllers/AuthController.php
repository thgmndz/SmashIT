<?php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login(): void {
        if (isset($_SESSION['crm_user'])) {
            header('Location: index.php?route=dashboard');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Por favor complete todos los campos.';
            } else {
                $user = $this->userModel->authenticate($username, $password);
                if ($user) {
                    $_SESSION['crm_user'] = $user;
                    header('Location: index.php?route=dashboard');
                    exit;
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void {
        unset($_SESSION['crm_user']);
        session_destroy();
        header('Location: index.php?route=login');
        exit;
    }

    public static function checkAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['crm_user'])) {
            header('Location: index.php?route=login');
            exit;
        }
    }
}
