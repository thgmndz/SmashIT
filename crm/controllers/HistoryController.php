<?php
require_once __DIR__ . '/../models/OrderModel.php';

class HistoryController {
    private OrderModel $orderModel;

    public function __construct() {
        if (($_GET['route'] ?? '') !== 'history_qr_mobile') {
            AuthController::checkAuth();
        }
        $this->orderModel = new OrderModel();
    }

    public function index(): void {
        $completedOrders = $this->orderModel->getCompletedOrders();
        require_once __DIR__ . '/../views/history/index.php';
    }

    public function qrReportMobile(): void {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $isMobile = preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', $userAgent);

        if (!$isMobile) {
            // Render non-mobile access restricted page
            require_once __DIR__ . '/../views/history/qr_report_restricted.php';
            return;
        }

        $completedOrders = $this->orderModel->getCompletedOrders();
        require_once __DIR__ . '/../views/history/qr_report_mobile.php';
    }
}
