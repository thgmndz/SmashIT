<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Gestión de Pedidos</h2>
    </div>

    <!-- Mensajes de Alerta -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-25 text-danger rounded-3" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-25 text-success rounded-3" role="alert">
            <i class="fa fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla de Pedidos -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha y Hora</th>
                        <th>Cliente / Contacto</th>
                        <th>Tipo y Pago</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se han registrado pedidos en la tienda.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): 
                            $statusClass = 'bg-secondary';
                            if ($order['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                            elseif ($order['status'] === 'preparing') $statusClass = 'bg-info text-dark';
                            elseif ($order['status'] === 'ready') $statusClass = 'bg-primary';
                            elseif ($order['status'] === 'completed') $statusClass = 'bg-success';
                            elseif ($order['status'] === 'cancelled') $statusClass = 'bg-danger';

                            // Obtener ítems del pedido
                            $db = CRMDatabase::getConnection();
                            $stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
                            $stmtItems->execute([':order_id' => $order['id']]);
                            $orderItems = $stmtItems->fetchAll();
                            $itemsJson = json_encode($orderItems);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?> hs</small>
                                </td>
                                <td>
                                    <div class="text-white font-weight-semibold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                </td>
                                <td>
                                    <div>
                                        <?php 
                                        if ($order['delivery_type'] === 'delivery') {
                                            echo '🚴 Delivery';
                                        } elseif ($order['delivery_type'] === 'pickup') {
                                            echo '🛍️ Retiro Local';
                                        } else {
                                            echo '🍽️ Consumo Local';
                                        }
                                        ?>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['payment_method']); ?></small>
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
                                    
                                    <a href="print.php?id=<?php echo $order['id']; ?>&type=ticket" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1" title="Imprimir Ticket">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-3"
                                        onclick="openUpdateOrderStatusModal(
                                            '<?php echo $order['id']; ?>',
                                            '<?php echo $order['status']; ?>'
                                        )">
                                        Estado
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

<!-- Modal Cambiar Estado de Pedido -->
<div class="modal fade" id="updateOrderStatusModal" tabindex="-1" aria-labelledby="updateOrderStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=orders" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="status_order_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h6 class="modal-title font-weight-bold" id="updateOrderStatusModalLabel">Cambiar Estado</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="status_select" class="form-label text-muted small">Seleccionar Estado</label>
                        <select name="status" id="status_select" class="form-select form-control-crm">
                            <option value="pending">PENDIENTE ⏳</option>
                            <option value="preparing">EN PREPARACIÓN 🍳</option>
                            <option value="ready">LISTO PARA ENTREGA 📦</option>
                            <option value="completed">COMPLETADO ✅</option>
                            <option value="cancelled">CANCELADO ❌</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25 justify-content-center">
                    <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-crm-primary rounded-3">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalles del Pedido (Igual al del Dashboard) -->
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
