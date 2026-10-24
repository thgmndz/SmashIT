<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Dashboard CRM</h2>
        <span class="badge bg-secondary px-3 py-2 rounded-3"><?php echo date('d/m/Y H:i'); ?></span>
    </div>

    <!-- Cards de Métricas -->
    <div class="row g-4 mb-5">
        <!-- Ventas Totales -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card glass-panel p-4 rounded-4 d-flex align-items-center">
                <div class="stat-icon bg-success-soft me-4">
                    <i class="fa fa-dollar-sign"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Ventas Totales</h6>
                    <h3 class="mb-0 text-white font-weight-bold"><?php echo number_format($totalSales, 0, ',', '.'); ?> Gs.</h3>
                    <small class="text-success text-xs font-weight-semibold">Pedidos completados</small>
                </div>
            </div>
        </div>

        <!-- Pedidos Totales -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card glass-panel p-4 rounded-4 d-flex align-items-center">
                <div class="stat-icon bg-primary-soft me-4">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Pedidos Totales</h6>
                    <h3 class="mb-0 text-white font-weight-bold"><?php echo $totalOrders; ?></h3>
                    <small class="text-muted text-xs">Registrados en tienda</small>
                </div>
            </div>
        </div>

        <!-- Clientes Totales -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card glass-panel p-4 rounded-4 d-flex align-items-center">
                <div class="stat-icon bg-info-soft me-4">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Clientes</h6>
                    <h3 class="mb-0 text-white font-weight-bold"><?php echo $totalCustomers; ?></h3>
                    <small class="text-muted text-xs">Base de datos CRM</small>
                </div>
            </div>
        </div>

        <!-- Productos Catálogo -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card glass-panel p-4 rounded-4 d-flex align-items-center">
                <div class="stat-icon bg-warning-soft me-4">
                    <i class="fa fa-hamburger"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small tracking-wider">Productos</h6>
                    <h3 class="mb-0 text-white font-weight-bold"><?php echo $totalProducts; ?></h3>
                    <small class="text-muted text-xs">Hamburguesas y sides</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ventas Diarias -->
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="glass-panel p-4 rounded-4">
                <h5 class="mb-4 font-weight-semibold">📈 Historial de Ventas por Día (Mes Actual)</h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('dailySalesChart').getContext('2d');
        var salesData = <?php echo json_encode($dailySales); ?>;
        
        var labels = [];
        var quantities = [];
        var amounts = [];
        
        if (salesData.length === 0) {
            labels.push("Sin datos");
            quantities.push(0);
            amounts.push(0);
        } else {
            salesData.forEach(function(item) {
                var parts = item.sale_date.split('-');
                var formattedDate = parts[2] + '/' + parts[1];
                labels.push(formattedDate);
                quantities.push(parseInt(item.qty, 10));
                amounts.push(parseFloat(item.total_amount));
            });
        }
        
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Cantidad de Pedidos',
                        data: quantities,
                        borderColor: '#ffb142',
                        backgroundColor: 'rgba(255, 177, 66, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Monto Recaudado (Gs.)',
                        data: amounts,
                        borderColor: '#ff4757',
                        backgroundColor: 'rgba(255, 71, 87, 0.05)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#9e9ea7'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#9e9ea7'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#9e9ea7',
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Cantidad de Pedidos',
                            color: '#ffb142'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#9e9ea7',
                            callback: function(value) {
                                return value.toLocaleString() + ' Gs.';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Recaudación (Gs.)',
                            color: '#ff4757'
                        }
                    }
                }
            }
        });
    });
    </script>

    <!-- Productos Más Vendidos del Mes -->
    <div class="row g-4 mt-4 mb-4">
        <div class="col-12">
            <div class="glass-panel p-4 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 font-weight-semibold">🔥 Top 10 Productos Más Vendidos de este Mes</h5>
                    <span class="badge bg-warning-soft text-warning px-3 py-1.5 rounded-3 text-xs fw-bold">Ranking de Ventas</span>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">Puesto</th>
                                <th>Producto</th>
                                <th class="text-center" style="width: 150px;">Cantidad Vendida</th>
                                <th class="text-end" style="width: 200px;">Total Recaudado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topProducts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No se han registrado ventas de productos en este mes.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($topProducts as $index => $prod): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($index === 0): ?>
                                                <span class="badge bg-warning text-dark fs-6" style="border-radius: 50%; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">🥇</span>
                                            <?php elseif ($index === 1): ?>
                                                <span class="badge bg-secondary text-dark fs-6" style="border-radius: 50%; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">🥈</span>
                                            <?php elseif ($index === 2): ?>
                                                <span class="badge bg-danger text-white fs-6" style="border-radius: 50%; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;">🥉</span>
                                            <?php else: ?>
                                                <span class="badge bg-dark text-white border border-secondary border-opacity-25" style="border-radius: 50%; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($prod['product_name']); ?></strong>
                                        </td>
                                        <td class="text-center fw-bold text-info fs-5">
                                            <?php echo $prod['total_qty']; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?php echo number_format($prod['total_revenue'], 0, ',', '.'); ?> Gs.
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Tablas y Estadísticas -->
    <div class="row g-4 mt-4 mb-4">
        <!-- Pedidos Recientes -->
        <div class="col-12 col-xl-8">
            <div class="glass-panel p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 font-weight-semibold">Últimos Pedidos Recibidos</h5>
                    <a href="index.php?route=orders" class="btn btn-sm btn-crm-primary rounded-3 text-xs">Ver todos</a>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No se han registrado pedidos recientes.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): 
                                    $statusClass = 'bg-secondary';
                                    if ($order['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                                    elseif ($order['status'] === 'preparing') $statusClass = 'bg-info text-dark';
                                    elseif ($order['status'] === 'ready') $statusClass = 'bg-primary';
                                    elseif ($order['status'] === 'completed') $statusClass = 'bg-success';
                                    elseif ($order['status'] === 'cancelled') $statusClass = 'bg-danger';
                                    
                                    $db = CRMDatabase::getConnection();
                                    $stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
                                    $stmtItems->execute([':order_id' => $order['id']]);
                                    $orderItems = $stmtItems->fetchAll();
                                    $itemsJson = json_encode($orderItems);
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($order['delivery_type'] === 'delivery') {
                                                echo '🚴 Delivery';
                                            } elseif ($order['delivery_type'] === 'pickup') {
                                                echo '🛍️ Local';
                                            } else {
                                                echo '🍽️ Consumo Local';
                                            }
                                            ?>
                                        </td>
                                        <td><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> Gs.</strong></td>
                                        <td>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo strtoupper($order['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-dark border border-secondary border-opacity-25 rounded-3 me-1"
                                                onclick="openOrderDetailsModal(
                                                    '<?php echo $order['order_code']; ?>',
                                                    '<?php echo addslashes($order['customer_name']); ?>',
                                                    '<?php echo $order['customer_phone']; ?>',
                                                    '<?php echo $order['customer_email']; ?>',
                                                    '<?php echo $order['delivery_type']; ?>',
                                                    '<?php echo addslashes($order['delivery_address'] ?? ''); ?>',
                                                    '<?php echo $order['payment_method']; ?>',
                                                    '<?php echo $order['subtotal']; ?>',
                                                    '<?php echo $order['delivery_fee']; ?>',
                                                    '<?php echo $order['total_amount']; ?>',
                                                    '<?php echo $order['status']; ?>',
                                                    '<?php echo addslashes($order['notes'] ?? ''); ?>',
                                                    '<?php echo htmlspecialchars($itemsJson, ENT_QUOTES, 'UTF-8'); ?>'
                                                )">
                                                <i class="fa fa-eye"></i> Detalle
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Distribución de Pedidos -->
        <div class="col-12 col-xl-4">
            <div class="glass-panel p-4 rounded-4 h-100">
                <h5 class="mb-4 font-weight-semibold">Estado de Pedidos</h5>
                <div class="list-group list-group-flush bg-transparent">
                    <?php 
                    $statusesMapping = [
                        'pending' => ['label' => 'Pendientes ⏳', 'class' => 'bg-warning text-dark'],
                        'preparing' => ['label' => 'En Preparación 🍳', 'class' => 'bg-info text-dark'],
                        'ready' => ['label' => 'Listos para Entrega 📦', 'class' => 'bg-primary'],
                        'completed' => ['label' => 'Completados ✅', 'class' => 'bg-success'],
                        'cancelled' => ['label' => 'Cancelados ❌', 'class' => 'bg-danger']
                    ];
                    
                    $statsByStatus = [];
                    foreach ($statusStats as $stat) {
                        $statsByStatus[$stat['status']] = $stat;
                    }

                    foreach ($statusesMapping as $key => $props):
                        $count = $statsByStatus[$key]['count'] ?? 0;
                        $total = $statsByStatus[$key]['total'] ?? 0;
                    ?>
                        <div class="list-group-item bg-transparent text-white border-secondary border-opacity-25 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge <?php echo $props['class']; ?> me-3 px-2.5 py-1.5" style="width: 110px; display: inline-block; text-align: center;">
                                    <?php echo strtoupper($key); ?>
                                </span>
                                <span class="small text-muted"><?php echo $props['label']; ?></span>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-0 font-weight-bold text-white"><?php echo $count; ?> pedidos</h6>
                                <small class="text-muted"><?php echo number_format($total, 0, ',', '.'); ?> Gs.</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalles del Pedido -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title font-weight-bold" id="orderDetailsModalLabel">Pedido: <span id="detail_order_code" class="text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted text-uppercase small mb-2">Información del Cliente</h6>
                        <p class="mb-1"><i class="fa fa-user me-2 text-warning"></i> <span id="detail_customer_name"></span></p>
                        <p class="mb-1"><i class="fa fa-phone me-2 text-warning"></i> <span id="detail_customer_phone"></span></p>
                        <p class="mb-0"><i class="fa fa-envelope me-2 text-warning"></i> <span id="detail_customer_email"></span></p>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted text-uppercase small mb-2">Detalles de Entrega y Pago</h6>
                        <p class="mb-1"><i class="fa fa-truck me-2 text-warning"></i> <span id="detail_delivery_type"></span></p>
                        <p class="mb-1"><i class="fa fa-map-marker-alt me-2 text-warning"></i> <span id="detail_delivery_address"></span></p>
                        <p class="mb-0"><i class="fa fa-credit-card me-2 text-warning"></i> Pago: <span id="detail_payment_method"></span></p>
                    </div>
                </div>

                <h6 class="text-muted text-uppercase small mb-3">Productos Pedidos</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-dark table-striped table-hover custom-table">
                        <thead>
                            <tr>
                                <th>Producto / Opciones</th>
                                <th class="text-center" style="width: 80px;">Cant.</th>
                                <th class="text-end" style="width: 130px;">Unitario</th>
                                <th class="text-end" style="width: 130px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail_items_body">
                            <!-- Injected JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end text-muted border-0">Subtotal:</td>
                                <td class="text-end border-0" id="detail_subtotal">0 Gs.</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end text-muted border-0">Costo Delivery:</td>
                                <td class="text-end border-0" id="detail_delivery_fee">0 Gs.</td>
                            </tr>
                            <tr class="fw-bold fs-5">
                                <td colspan="3" class="text-end text-warning border-0">Total:</td>
                                <td class="text-end text-warning border-0" id="detail_total">0 Gs.</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div>
                    <h6 class="text-muted text-uppercase small mb-2">Notas Especiales / Observaciones</h6>
                    <div class="bg-secondary bg-opacity-25 p-3 rounded-3 border border-secondary border-opacity-50">
                        <p class="mb-0 text-white-50" id="detail_notes"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <div id="detail_status"></div>
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
