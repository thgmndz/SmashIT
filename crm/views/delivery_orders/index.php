<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Procesamiento de Delivery</h2>
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

    <!-- Tabla de Pedidos de Delivery -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Código Pedido</th>
                        <th>Fecha y Hora</th>
                        <th>Cliente / Contacto</th>
                        <th>Dirección de Envío</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado Delivery</th>
                        <th>Chofer Asignado</th>
                        <th class="text-center" style="width: 250px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay pedidos registrados para envío a domicilio (Delivery).</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $od): ?>
                            <?php 
                                $statusClass = 'bg-secondary';
                                if ($od['delivery_status'] === 'pendiente') $statusClass = 'bg-warning text-dark';
                                elseif ($od['delivery_status'] === 'en_camino') $statusClass = 'bg-info text-dark';
                                elseif ($od['delivery_status'] === 'entregado') $statusClass = 'bg-success';
                                elseif ($od['delivery_status'] === 'devuelto') $statusClass = 'bg-danger';
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($od['order_code']); ?></strong></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($od['created_at'])); ?></div>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($od['created_at'])); ?> hs</small>
                                </td>
                                <td>
                                    <div class="text-white font-weight-semibold"><?php echo htmlspecialchars($od['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($od['customer_phone']); ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $addressText = $od['delivery_address'] ?: 'No especificada';
                                        $mapLink = '';
                                        if (preg_match('/(https?:\/\/[^\s\]]+)/', $addressText, $matches)) {
                                            $mapLink = $matches[1];
                                        }
                                    ?>
                                    <?php if ($mapLink): ?>
                                        <a href="<?php echo htmlspecialchars($mapLink); ?>" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold" style="padding: 4px 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fa fa-map-marked-alt"></i> Ver en Mapa 📍
                                        </a>
                                    <?php else: ?>
                                        <div class="text-white text-wrap" style="max-width: 250px; font-size: 0.88rem; line-height: 1.3;">
                                            <?php echo htmlspecialchars(trim($addressText)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end font-weight-bold text-white"><?php echo number_format($od['total_amount'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-center">
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo strtoupper($od['delivery_status'] ?: 'PENDIENTE'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($od['driver_name']): ?>
                                        <div class="text-white font-weight-semibold"><i class="fa fa-motorcycle me-1 text-primary"></i> <?php echo htmlspecialchars($od['driver_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($od['driver_phone']); ?></small>
                                    <?php else: ?>
                                        <span class="text-danger small"><i class="fa fa-exclamation-circle me-1"></i> No Asignado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Botón de Asignar Chofer -->
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3 me-1"
                                        onclick="openAssignDriverModal('<?php echo $od['id']; ?>', '<?php echo $od['delivery_driver_id']; ?>')">
                                        <i class="fa fa-user-plus"></i> Chofer
                                    </button>

                                    <!-- Botón de Actualizar Estado -->
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-3 me-1"
                                        onclick="openDeliveryStatusModal('<?php echo $od['id']; ?>', '<?php echo $od['delivery_status']; ?>')">
                                        Estado
                                    </button>

                                    <!-- Botón de Imprimir Comprobante -->
                                    <a href="print.php?id=<?php echo $od['id']; ?>&type=ticket" target="_blank" class="btn btn-sm btn-outline-success rounded-3" title="Imprimir Ticket">
                                        <i class="fa fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Asignar Chofer -->
<div class="modal fade" id="assignDriverModal" tabindex="-1" aria-labelledby="assignDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=delivery_orders" method="POST">
                <input type="hidden" name="action" value="assign_driver">
                <input type="hidden" name="order_id" id="assign_order_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h6 class="modal-title font-weight-bold" id="assignDriverModalLabel">Asignar Chofer</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="driver_select" class="form-label text-muted small">Seleccionar Chofer *</label>
                        <select name="driver_id" id="driver_select" class="form-select form-control-crm" required>
                            <option value="">Seleccione Chofer</option>
                            <?php foreach ($drivers as $dr): ?>
                                <option value="<?php echo $dr['id']; ?>"><?php echo htmlspecialchars($dr['nombre']); ?> (<?php echo htmlspecialchars($dr['vehiculo']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary btn-sm rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary btn-sm rounded-3">Confirmar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado de Delivery -->
<div class="modal fade" id="deliveryStatusModal" tabindex="-1" aria-labelledby="deliveryStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=delivery_orders" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="status_order_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h6 class="modal-title font-weight-bold" id="deliveryStatusModalLabel">Cambiar Estado Delivery</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="delivery_status_select" class="form-label text-muted small">Seleccionar Estado</label>
                        <select name="status" id="delivery_status_select" class="form-select form-control-crm" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_camino">En Camino</option>
                            <option value="entregado">Entregado</option>
                            <option value="devuelto">Devuelto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary btn-sm rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary btn-sm rounded-3">Guardar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAssignDriverModal(orderId, currentDriverId) {
    document.getElementById('assign_order_id').value = orderId;
    document.getElementById('driver_select').value = currentDriverId || '';
    
    var modal = new bootstrap.Modal(document.getElementById('assignDriverModal'));
    modal.show();
}

function openDeliveryStatusModal(orderId, currentStatus) {
    document.getElementById('status_order_id').value = orderId;
    document.getElementById('delivery_status_select').value = currentStatus || 'pendiente';
    
    var modal = new bootstrap.Modal(document.getElementById('deliveryStatusModal'));
    modal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
