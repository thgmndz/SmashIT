<?php
require_once __DIR__ . '/../models/OrderModel.php';

class PaymentController {
    private OrderModel $orderModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        $reportData = $this->orderModel->getPaymentReport();
        require_once __DIR__ . '/../views/payments/index.php';
    }
}
