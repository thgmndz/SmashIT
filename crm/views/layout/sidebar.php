<?php
$currentRoute = $_GET['route'] ?? 'dashboard';
?>
<div id="sidebar">
    <div class="sidebar-header text-center">
        <img src="../logo.png" alt="SmashIT Burgers Logo" class="img-fluid mb-2 logo-dark" style="max-height: 70px; object-fit: contain;">
        <img src="../logo_light.png" alt="SmashIT Burgers Logo" class="img-fluid mb-2 logo-light" style="max-height: 70px; object-fit: contain; display: none;">
        <h4 class="text-white mb-0 font-weight-bold" style="font-size: 1.1rem; letter-spacing: 1px;">SmashIT <span style="color: var(--primary-color);">CRM</span></h4>
    </div>
    
    <div class="sidebar-menu">
        <a href="index.php?route=dashboard" class="<?php echo $currentRoute === 'dashboard' ? 'active' : ''; ?>">
            <i class="fa fa-chart-pie"></i>
            <span>Dashboard</span>
        </a>
        <a href="index.php?route=pos" class="<?php echo $currentRoute === 'pos' ? 'active' : ''; ?>">
            <i class="fa fa-cash-register"></i>
            <span>Caja (POS)</span>
        </a>
        <a href="index.php?route=cash_registers_crud" class="<?php echo $currentRoute === 'cash_registers_crud' ? 'active' : ''; ?>">
            <i class="fa fa-key"></i>
            <span>Apertura y Cierre</span>
        </a>
        <a href="index.php?route=arqueos" class="<?php echo $currentRoute === 'arqueos' ? 'active' : ''; ?>">
            <i class="fa fa-calculator"></i>
            <span>Arqueo de Caja</span>
        </a>
        <a href="index.php?route=recaudaciones" class="<?php echo $currentRoute === 'recaudaciones' ? 'active' : ''; ?>">
            <i class="fa fa-piggy-bank"></i>
            <span>Recaudaciones</span>
        </a>
        <a href="index.php?route=history" class="<?php echo $currentRoute === 'history' ? 'active' : ''; ?>">
            <i class="fa fa-history"></i>
            <span>Historial Cobros</span>
        </a>
        <a href="index.php?route=payments" class="<?php echo $currentRoute === 'payments' ? 'active' : ''; ?>">
            <i class="fa fa-credit-card"></i>
            <span>Pagos Realizados</span>
        </a>
        <a href="index.php?route=invoices" class="<?php echo $currentRoute === 'invoices' ? 'active' : ''; ?>">
            <i class="fa fa-file-invoice-dollar"></i>
            <span>Facturación</span>
        </a>
        <a href="index.php?route=orders" class="<?php echo $currentRoute === 'orders' ? 'active' : ''; ?>">
            <i class="fa fa-shopping-bag"></i>
            <span>Pedidos</span>
        </a>
        <a href="index.php?route=customers" class="<?php echo $currentRoute === 'customers' ? 'active' : ''; ?>">
            <i class="fa fa-users"></i>
            <span>Clientes</span>
        </a>
        <a href="index.php?route=tables" class="<?php echo $currentRoute === 'tables' ? 'active' : ''; ?>">
            <i class="fa fa-chair"></i>
            <span>Mesas</span>
        </a>
        <a href="index.php?route=products" class="<?php echo $currentRoute === 'products' ? 'active' : ''; ?>">
            <i class="fa fa-hamburger"></i>
            <span>Productos</span>
        </a>
        
        <div class="border-top border-secondary my-2 mx-3"></div>
        <div class="px-3 py-1"><small class="text-muted text-uppercase">Compras</small></div>
        
        <a href="index.php?route=proveedores" class="<?php echo $currentRoute === 'proveedores' ? 'active' : ''; ?>">
            <i class="fa fa-truck"></i>
            <span>Proveedores</span>
        </a>
        <a href="index.php?route=pedidos_compra" class="<?php echo $currentRoute === 'pedidos_compra' ? 'active' : ''; ?>">
            <i class="fa fa-clipboard-list"></i>
            <span>Pedidos Compra</span>
        </a>
        <a href="index.php?route=presupuestos_compra" class="<?php echo $currentRoute === 'presupuestos_compra' ? 'active' : ''; ?>">
            <i class="fa fa-file-signature"></i>
            <span>Presupuestos</span>
        </a>
        <a href="index.php?route=ordenes_compra" class="<?php echo $currentRoute === 'ordenes_compra' ? 'active' : ''; ?>">
            <i class="fa fa-file-invoice"></i>
            <span>Órdenes Compra</span>
        </a>
        <a href="index.php?route=facturas_compra" class="<?php echo $currentRoute === 'facturas_compra' ? 'active' : ''; ?>">
            <i class="fa fa-file-invoice-dollar"></i>
            <span>Facturas Compra</span>
        </a>

        <div class="border-top border-secondary my-2 mx-3"></div>
        <div class="px-3 py-1"><small class="text-muted text-uppercase">Delivery</small></div>
        
        <a href="index.php?route=delivery_drivers" class="<?php echo $currentRoute === 'delivery_drivers' ? 'active' : ''; ?>">
            <i class="fa fa-motorcycle"></i>
            <span>Choferes Delivery</span>
        </a>
        <a href="index.php?route=delivery_orders" class="<?php echo $currentRoute === 'delivery_orders' ? 'active' : ''; ?>">
            <i class="fa fa-shipping-fast"></i>
            <span>Pedidos Delivery</span>
        </a>
        <a href="index.php?route=delivery_report" class="<?php echo $currentRoute === 'delivery_report' ? 'active' : ''; ?>">
            <i class="fa fa-file-invoice"></i>
            <span>Informe Repartos</span>
        </a>



        <div class="border-top border-secondary my-3 mx-3"></div>
        
        <a href="../index.php" target="_blank">
            <i class="fa fa-external-link-alt"></i>
            <span>Ver Tienda</span>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar) {
        var savedScrollTop = sessionStorage.getItem('sidebar_scroll_top');
        if (savedScrollTop) {
            sidebar.scrollTop = parseInt(savedScrollTop, 10);
        }
        
        sidebar.addEventListener('scroll', function() {
            sessionStorage.setItem('sidebar_scroll_top', sidebar.scrollTop);
        });

        var links = sidebar.querySelectorAll('.sidebar-menu a');
        links.forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('sidebar_scroll_top', sidebar.scrollTop);
            });
        });
    }
});
</script>
