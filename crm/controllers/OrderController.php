<?php
require_once __DIR__ . '/../models/OrderModel.php';

class OrderController {
    private OrderModel $orderModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        $orders = $this->orderModel->getAll();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_status') {
                $id = (int)($_POST['id'] ?? 0);
                $status = trim($_POST['status'] ?? '');

                $validStatuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
                if ($id > 0 && in_array($status, $validStatuses)) {
                    $result = $this->orderModel->updateStatus($id, $status);
                    if ($result) {
                        $success = 'Estado del pedido actualizado correctamente.';
                        $orders = $this->orderModel->getAll();
                    } else {
                        $error = 'Error al actualizar el estado del pedido.';
                    }
                } else {
                    $error = 'Parámetros inválidos.';
                }
            }
        }

        require_once __DIR__ . '/../views/orders/index.php';
    }
}
