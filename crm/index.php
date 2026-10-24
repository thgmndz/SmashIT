<?php
// Enrutador centralizado (Front Controller) para el CRM de SmashIT Burgers
session_start();

// Configuración y Base de Datos
require_once __DIR__ . '/config/db.php';

// Cargar Controladores
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/CustomerController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/TableController.php';
require_once __DIR__ . '/controllers/POSController.php';
require_once __DIR__ . '/controllers/HistoryController.php';
require_once __DIR__ . '/controllers/InvoiceController.php';
require_once __DIR__ . '/controllers/ProveedorController.php';
require_once __DIR__ . '/controllers/PedidoCompraController.php';
require_once __DIR__ . '/controllers/PresupuestoCompraController.php';
require_once __DIR__ . '/controllers/OrdenCompraController.php';
require_once __DIR__ . '/controllers/FacturaCompraController.php';
require_once __DIR__ . '/controllers/CashRegisterCRUDController.php';
require_once __DIR__ . '/controllers/ArqueoCajaController.php';
require_once __DIR__ . '/controllers/RecaudacionController.php';
require_once __DIR__ . '/controllers/DeliveryDriverController.php';
require_once __DIR__ . '/controllers/DeliveryProcessController.php';
require_once __DIR__ . '/controllers/PaymentController.php';
require_once __DIR__ . '/controllers/DeliveryReportController.php';




// Obtener la ruta solicitada
$route = $_GET['route'] ?? 'dashboard';

// Enrutamiento
switch ($route) {
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'dashboard':
        $controller = new DashboardController();
        $controller->index();
        break;

    case 'pos':
        $controller = new POSController();
        $controller->index();
        break;

    case 'history':
        $controller = new HistoryController();
        $controller->index();
        break;

    case 'history_qr_mobile':
        $controller = new HistoryController();
        $controller->qrReportMobile();
        break;

    case 'payments':
        $controller = new PaymentController();
        $controller->index();
        break;

    case 'invoices':
        $controller = new InvoiceController();
        $controller->index();
        break;

    case 'customers':
        $controller = new CustomerController();
        $controller->index();
        break;

    case 'tables':
        $controller = new TableController();
        $controller->index();
        break;

    case 'products':
        $controller = new ProductController();
        $controller->index();
        break;

    case 'orders':
        $controller = new OrderController();
        $controller->index();
        break;

    case 'proveedores':
        $controller = new ProveedorController();
        $controller->index();
        break;

    case 'pedidos_compra':
        $controller = new PedidoCompraController();
        $controller->index();
        break;

    case 'presupuestos_compra':
        $controller = new PresupuestoCompraController();
        $controller->index();
        break;

    case 'ordenes_compra':
        $controller = new OrdenCompraController();
        $controller->index();
        break;

    case 'facturas_compra':
        $controller = new FacturaCompraController();
        $controller->index();
        break;

    case 'cash_registers_crud':
        $controller = new CashRegisterCRUDController();
        $controller->index();
        break;

    case 'arqueos':
        $controller = new ArqueoCajaController();
        $controller->index();
        break;

    case 'recaudaciones':
        $controller = new RecaudacionController();
        $controller->index();
        break;

    case 'delivery_drivers':
        $controller = new DeliveryDriverController();
        $controller->index();
        break;

    case 'delivery_orders':
        $controller = new DeliveryProcessController();
        $controller->index();
        break;

    case 'delivery_report':
        $controller = new DeliveryReportController();
        $controller->index();
        break;





    default:
        // Si no existe la ruta, redirige al dashboard
        header('Location: index.php?route=dashboard');
        exit;
}
