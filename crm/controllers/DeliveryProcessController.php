<?php
require_once __DIR__ . '/../models/DeliveryOrderModel.php';
require_once __DIR__ . '/../models/DeliveryDriverModel.php';

class DeliveryProcessController {
    private DeliveryOrderModel $model;
    private DeliveryDriverModel $driverModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new DeliveryOrderModel();
        $this->driverModel = new DeliveryDriverModel();
    }

    public function index(): void {
        $orders = $this->model->getDeliveryOrders();
        $drivers = $this->driverModel->getActive();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'assign_driver') {
                $order_id = (int)($_POST['order_id'] ?? 0);
                $driver_id = (int)($_POST['driver_id'] ?? 0);

                if ($order_id <= 0 || $driver_id <= 0) {
                    $error = 'Debe seleccionar un Chofer para el pedido.';
                } else {
                    $result = $this->model->assignDriver($order_id, $driver_id);
                    if ($result) {
                        $success = 'Chofer asignado correctamente. Pedido en camino.';
                        $orders = $this->model->getDeliveryOrders();
                    } else {
                        $error = 'Error al asignar chofer.';
                    }
                }
            } elseif ($action === 'update_status') {
                $order_id = (int)($_POST['order_id'] ?? 0);
                $status = trim($_POST['status'] ?? '');

                if ($order_id <= 0 || empty($status)) {
                    $error = 'Datos incompletos.';
                } else {
                    $result = $this->model->updateDeliveryStatus($order_id, $status);
                    if ($result) {
                        $success = 'Estado del delivery actualizado.';
                        $orders = $this->model->getDeliveryOrders();
                    } else {
                        $error = 'Error al actualizar estado.';
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/delivery_orders/index.php';
    }
}
