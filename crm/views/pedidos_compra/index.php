<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Pedidos de Compra</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createPedidoModal">
            <i class="fa fa-plus me-2"></i> Nuevo Pedido
        </button>
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

    <!-- Tabla de Pedidos de Compra -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th>Observación</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay pedidos de compra registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pd): ?>
                            <tr>
                                <td>
                                    <div class="font-weight-semibold text-white"><?php echo htmlspecialchars($pd['sucursal']); ?></div>
                                    <small class="text-muted">Fecha: <?php echo date('d/m/Y', strtotime($pd['created_at'])); ?></small>
                                </td>
                                <td><span class="text-white"><?php echo htmlspecialchars($pd['producto']); ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-primary px-3 rounded-pill"><?php echo (int)$pd['cantidad']; ?></span>
                                </td>
                                <td><span class="text-muted text-truncate d-inline-block" style="max-width: 300px;"><?php echo htmlspecialchars($pd['observacion'] ?: 'N/A'); ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditPedidoModal(
                                            '<?php echo $pd['id']; ?>',
                                            '<?php echo addslashes($pd['sucursal']); ?>',
                                            '<?php echo addslashes($pd['observacion'] ?? ''); ?>',
                                            '<?php echo addslashes($pd['producto']); ?>',
                                            '<?php echo $pd['cantidad']; ?>'
                                        )">
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                    <a href="print_purchase.php?type=pedido&id=<?php echo $pd['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeletePedidoModal(
                                            '<?php echo $pd['id']; ?>',
                                            '<?php echo addslashes($pd['sucursal'] . ' (' . $pd['producto'] . ')'); ?>'
                                        )">
                                        <i class="fa fa-trash"></i>
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

<!-- Modal Registrar Pedido -->
<div class="modal fade" id="createPedidoModal" tabindex="-1" aria-labelledby="createPedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=pedidos_compra" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createPedidoModalLabel">Registrar Pedido de Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="sucursal" class="form-label text-muted small">Sucursal *</label>
                        <select name="sucursal" id="sucursal" class="form-select form-control-crm" required>
                            <option value="">Seleccione una Sucursal</option>
                            <option value="Sucursal Asunción">Sucursal Asunción</option>
                            <option value="Sucursal Luque">Sucursal Luque</option>
                            <option value="Sucursal San Lorenzo">Sucursal San Lorenzo</option>
                            <option value="Sucursal Lambaré">Sucursal Lambaré</option>
                            <option value="Sucursal Fernando de la Mora">Sucursal Fernando de la Mora</option>
                            <option value="Sucursal Capiatá">Sucursal Capiatá</option>
                            <option value="Sucursal Mariano Roque Alonso">Sucursal Mariano Roque Alonso</option>
                            <option value="Sucursal Villa Elisa">Sucursal Villa Elisa</option>
                            <option value="Sucursal Itauguá">Sucursal Itauguá</option>
                            <option value="Sucursal Limpio">Sucursal Limpio</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="producto" class="form-label text-muted small">Producto *</label>
                        <input type="text" name="producto" id="producto" class="form-control form-control-crm" placeholder="Ej. Pan Brioche, Papas Congeladas..." required>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label text-muted small">Cantidad *</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control form-control-crm" min="1" placeholder="Ej. 100" required>
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label text-muted small">Observación</label>
                        <textarea name="observacion" id="observacion" rows="3" class="form-control form-control-crm" placeholder="Observaciones adicionales sobre el pedido..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Pedido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Pedido -->
<div class="modal fade" id="editPedidoModal" tabindex="-1" aria-labelledby="editPedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=pedidos_compra" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editPedidoModalLabel">Modificar Pedido de Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_sucursal" class="form-label text-muted small">Sucursal *</label>
                        <select name="sucursal" id="edit_sucursal" class="form-select form-control-crm" required>
                            <option value="Sucursal Asunción">Sucursal Asunción</option>
                            <option value="Sucursal Luque">Sucursal Luque</option>
                            <option value="Sucursal San Lorenzo">Sucursal San Lorenzo</option>
                            <option value="Sucursal Lambaré">Sucursal Lambaré</option>
                            <option value="Sucursal Fernando de la Mora">Sucursal Fernando de la Mora</option>
                            <option value="Sucursal Capiatá">Sucursal Capiatá</option>
                            <option value="Sucursal Mariano Roque Alonso">Sucursal Mariano Roque Alonso</option>
                            <option value="Sucursal Villa Elisa">Sucursal Villa Elisa</option>
                            <option value="Sucursal Itauguá">Sucursal Itauguá</option>
                            <option value="Sucursal Limpio">Sucursal Limpio</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_producto" class="form-label text-muted small">Producto *</label>
                        <input type="text" name="producto" id="edit_producto" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cantidad" class="form-label text-muted small">Cantidad *</label>
                        <input type="number" name="cantidad" id="edit_cantidad" class="form-control form-control-crm" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_observacion" class="form-label text-muted small">Observación</label>
                        <textarea name="observacion" id="edit_observacion" rows="3" class="form-control form-control-crm"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="deletePedidoModal" tabindex="-1" aria-labelledby="deletePedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=pedidos_compra" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Pedido</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_pedido_name"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer y desasociará presupuestos vinculados.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 rounded-3">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditPedidoModal(id, sucursal, observacion, producto, cantidad) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_sucursal').value = sucursal;
    document.getElementById('edit_observacion').value = observacion;
    document.getElementById('edit_producto').value = producto;
    document.getElementById('edit_cantidad').value = cantidad;
    
    var editModal = new bootstrap.Modal(document.getElementById('editPedidoModal'));
    editModal.show();
}

function openDeletePedidoModal(id, desc) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_pedido_name').textContent = desc;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deletePedidoModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
