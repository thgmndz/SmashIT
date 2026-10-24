<?php
require_once __DIR__ . '/../models/DeliveryDriverModel.php';

class DeliveryReportController {
    private DeliveryDriverModel $driverModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->driverModel = new DeliveryDriverModel();
    }

    public function index(): void {
        $reportData = $this->driverModel->getDriverMonthlyReport();
        require_once __DIR__ . '/../views/delivery_report/index.php';
    }
}
