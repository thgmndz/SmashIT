<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Apertura y Cierre de Caja (Ventas)</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createCajaModal">
            <i class="fa fa-key me-2"></i> Abrir Nueva Caja
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

    <!-- Tabla de Cajas -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Caja ID</th>
                        <th>Funcionario (Cajero)</th>
                        <th class="text-end">Monto Apertura</th>
                        <th class="text-end">Monto Cierre</th>
                        <th class="text-end">Monto Calculado</th>
                        <th class="text-end">Diferencia</th>
                        <th class="text-center">Estado</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th class="text-center" style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cajas)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No hay registros de cajas en el sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cajas as $cj): ?>
                            <tr>
                                <td><span class="text-white font-weight-bold">Caja #<?php echo $cj['id']; ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($cj['employee_name'] ?: 'Desconocido'); ?></span></td>
                                <td class="text-end text-white"><?php echo number_format($cj['opening_amount'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-end text-white"><?php echo $cj['closing_amount'] !== null ? number_format($cj['closing_amount'], 0, ',', '.') . ' Gs.' : '<span class="text-muted">N/A</span>'; ?></td>
                                <td class="text-end text-white"><?php echo $cj['calculated_amount'] !== null ? number_format($cj['calculated_amount'], 0, ',', '.') . ' Gs.' : '<span class="text-muted">N/A</span>'; ?></td>
                                <td class="text-end font-weight-bold <?php echo ($cj['difference'] ?? 0) < 0 ? 'text-danger' : (($cj['difference'] ?? 0) > 0 ? 'text-warning' : 'text-success'); ?>">
                                    <?php echo $cj['difference'] !== null ? number_format($cj['difference'], 0, ',', '.') . ' Gs.' : '<span class="text-muted">N/A</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo $cj['status'] === 'open' ? 'bg-success' : 'bg-secondary'; ?> px-2 py-1">
                                        <?php echo $cj['status'] === 'open' ? 'ABIERTA' : 'CERRADA'; ?>
                                    </span>
                                </td>
                                <td><span class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($cj['opened_at'])); ?></span></td>
                                <td><span class="small text-muted"><?php echo $cj['closed_at'] ? date('d/m/Y H:i', strtotime($cj['closed_at'])) : '<span class="text-muted">Abierta</span>'; ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditCajaModal(
                                            '<?php echo $cj['id']; ?>',
                                            '<?php echo $cj['employee_id']; ?>',
                                            '<?php echo $cj['opening_amount']; ?>',
                                            '<?php echo $cj['closing_amount'] ?? ''; ?>',
                                            '<?php echo $cj['calculated_amount'] ?? ''; ?>',
                                            '<?php echo $cj['difference'] ?? ''; ?>',
                                            '<?php echo $cj['status']; ?>',
                                            '<?php echo date('Y-m-d\TH:i', strtotime($cj['opened_at'])); ?>',
                                            '<?php echo $cj['closed_at'] ? date('Y-m-d\TH:i', strtotime($cj['closed_at'])) : ''; ?>'
                                        )">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a href="print_purchase.php?type=caja&id=<?php echo $cj['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteCajaModal(
                                            '<?php echo $cj['id']; ?>',
                                            'Caja #<?php echo $cj['id']; ?>'
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

<!-- Modal Abrir Caja -->
<div class="modal fade" id="createCajaModal" tabindex="-1" aria-labelledby="createCajaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=cash_registers_crud" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createCajaModalLabel">Apertura de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label text-muted small">Cajero / Funcionario *</label>
                        <select name="employee_id" id="employee_id" class="form-select form-control-crm" required>
                            <option value="">Seleccione Funcionario</option>
                            <?php foreach ($funcionarios as $func): ?>
                                <option value="<?php echo $func['id']; ?>"><?php echo htmlspecialchars($func['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="opening_amount" class="form-label text-muted small">Monto de Apertura (Gs.) *</label>
                        <input type="number" name="opening_amount" id="opening_amount" class="form-control form-control-crm" min="0" value="100000" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Confirmar Apertura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Caja -->
<div class="modal fade" id="editCajaModal" tabindex="-1" aria-labelledby="editCajaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=cash_registers_crud" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editCajaModalLabel">Modificar Datos de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_employee_id" class="form-label text-muted small">Cajero / Funcionario *</label>
                            <select name="employee_id" id="edit_employee_id" class="form-select form-control-crm" required>
                                <?php foreach ($funcionarios as $func): ?>
                                    <option value="<?php echo $func['id']; ?>"><?php echo htmlspecialchars($func['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label text-muted small">Estado *</label>
                            <select name="status" id="edit_status" class="form-select form-control-crm" required>
                                <option value="open">Abierta</option>
                                <option value="closed">Cerrada</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_opening_amount" class="form-label text-muted small">Monto Apertura (Gs.) *</label>
                            <input type="number" name="opening_amount" id="edit_opening_amount" class="form-control form-control-crm" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_closing_amount" class="form-label text-muted small">Monto Cierre (Gs.)</label>
                            <input type="number" name="closing_amount" id="edit_closing_amount" class="form-control form-control-crm" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_calculated_amount" class="form-label text-muted small">Monto Calculado Sistema (Gs.)</label>
                            <input type="number" name="calculated_amount" id="edit_calculated_amount" class="form-control form-control-crm" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_difference" class="form-label text-muted small">Diferencia (Gs.)</label>
                            <input type="number" name="difference" id="edit_difference" class="form-control form-control-crm">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_opened_at" class="form-label text-muted small">Fecha Apertura *</label>
                            <input type="datetime-local" name="opened_at" id="edit_opened_at" class="form-control form-control-crm" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_closed_at" class="form-label text-muted small">Fecha Cierre</label>
                            <input type="datetime-local" name="closed_at" id="edit_closed_at" class="form-control form-control-crm">
                        </div>
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
<div class="modal fade" id="deleteCajaModal" tabindex="-1" aria-labelledby="deleteCajaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=cash_registers_crud" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Registro</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_caja_name"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer y borrará transacciones, arqueos y depósitos relacionados.</p>
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
function openEditCajaModal(id, employee_id, opening, closing, calculated, difference, status, opened, closed) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_employee_id').value = employee_id;
    document.getElementById('edit_opening_amount').value = opening;
    document.getElementById('edit_closing_amount').value = closing;
    document.getElementById('edit_calculated_amount').value = calculated;
    document.getElementById('edit_difference').value = difference;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_opened_at').value = opened;
    document.getElementById('edit_closed_at').value = closed;
    
    var editModal = new bootstrap.Modal(document.getElementById('editCajaModal'));
    editModal.show();
}

function openDeleteCajaModal(id, desc) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_caja_name').textContent = desc;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteCajaModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
