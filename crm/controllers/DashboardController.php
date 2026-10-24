<?php
require_once __DIR__ . '/../models/CustomerModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class DashboardController {
    private CustomerModel $customerModel;
    private OrderModel $orderModel;
    private ProductModel $productModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->customerModel = new CustomerModel();
        $this->orderModel = new OrderModel();
        $this->productModel = new ProductModel();
    }

    public function index(): void {
        $totalCustomers = $this->customerModel->getTotalCount();
        $totalOrders = $this->orderModel->getTotalCount();
        $totalSales = $this->orderModel->getTotalSales();
        $totalProducts = $this->productModel->getTotalCount();
        
        $recentOrders = $this->orderModel->getRecentOrders(5);
        $statusStats = $this->orderModel->getSalesByStatus();
        $topProducts = $this->orderModel->getTopProductsThisMonth(10);
        $dailySales = $this->orderModel->getDailySalesThisMonth();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
