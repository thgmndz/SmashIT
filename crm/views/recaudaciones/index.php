<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Recaudaciones a Depositar</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createRecaudacionModal">
            <i class="fa fa-piggy-bank me-2"></i> Registrar Depósito
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

    <!-- Tabla de Recaudaciones -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID Reg.</th>
                        <th>Caja Vinc.</th>
                        <th>Cajero / Funcionario</th>
                        <th>Banco</th>
                        <th>Nro. Boleta</th>
                        <th class="text-end">Monto Depositar</th>
                        <th class="text-center">Estado</th>
                        <th>Fecha Registro</th>
                        <th>Observación</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recaudaciones)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No hay registros de depósitos en el sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recaudaciones as $rc): ?>
                            <tr>
                                <td><span class="text-white font-weight-bold">Reg. #<?php echo $rc['id']; ?></span></td>
                                <td><span class="badge bg-info">Caja #<?php echo $rc['cash_register_id']; ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($rc['funcionario_nombre'] ?: 'N/A'); ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($rc['banco']); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($rc['nro_boleta']); ?></span></td>
                                <td class="text-end text-success font-weight-bold"><?php echo number_format($rc['monto_depositar'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-center">
                                    <span class="badge <?php echo $rc['estado'] === 'depositado' ? 'bg-success' : 'bg-warning text-dark'; ?> px-2 py-1">
                                        <?php echo strtoupper($rc['estado']); ?>
                                    </span>
                                </td>
                                <td><span class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($rc['fecha'])); ?></span></td>
                                <td><span class="text-muted text-truncate d-inline-block" style="max-width: 150px;"><?php echo htmlspecialchars($rc['observacion'] ?: 'N/A'); ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditRecaudacionModal(
                                            '<?php echo $rc['id']; ?>',
                                            '<?php echo $rc['cash_register_id']; ?>',
                                            '<?php echo $rc['monto_depositar']; ?>',
                                            '<?php echo addslashes($rc['banco']); ?>',
                                            '<?php echo addslashes($rc['nro_boleta']); ?>',
                                            '<?php echo addslashes($rc['observacion'] ?? ''); ?>',
                                            '<?php echo $rc['estado']; ?>'
                                        )">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a href="print_purchase.php?type=recaudacion&id=<?php echo $rc['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteRecaudacionModal(
                                            '<?php echo $rc['id']; ?>',
                                            'Boleta Nro. <?php echo htmlspecialchars($rc['nro_boleta']); ?>'
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

<!-- Modal Registrar Recaudación -->
<div class="modal fade" id="createRecaudacionModal" tabindex="-1" aria-labelledby="createRecaudacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=recaudaciones" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createRecaudacionModalLabel">Registrar Boleta de Depósito</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="cash_register_id" class="form-label text-muted small">Caja de Origen *</label>
                        <select name="cash_register_id" id="cash_register_id" class="form-select form-control-crm" required>
                            <option value="">Seleccione Caja</option>
                            <?php foreach ($cajas as $cj): ?>
                                <option value="<?php echo $cj['id']; ?>">
                                    Caja #<?php echo $cj['id']; ?> - <?php echo htmlspecialchars($cj['employee_name']); ?> (<?php echo date('d/m/Y', strtotime($cj['opened_at'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="banco" class="form-label text-muted small">Entidad Bancaria *</label>
                        <select name="banco" id="banco" class="form-select form-control-crm" required>
                            <option value="">Seleccione Banco</option>
                            <option value="Banco Itaú">Banco Itaú</option>
                            <option value="Banco Familiar">Banco Familiar</option>
                            <option value="Banco Continental">Banco Continental</option>
                            <option value="Sudameris Bank">Sudameris Bank</option>
                            <option value="Banco Basa">Banco Basa</option>
                            <option value="Banco Atlas">Banco Atlas</option>
                            <option value="Banco GNB Paraguay">Banco GNB Paraguay</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nro_boleta" class="form-label text-muted small">Número de Boleta *</label>
                        <input type="text" name="nro_boleta" id="nro_boleta" class="form-control form-control-crm" placeholder="Ej. DEP-9988" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto_depositar" class="form-label text-muted small">Monto a Depositar (Gs.) *</label>
                        <input type="number" name="monto_depositar" id="monto_depositar" class="form-control form-control-crm" min="1" value="100000" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label text-muted small">Estado de Depósito *</label>
                        <select name="estado" id="estado" class="form-select form-control-crm" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="depositado">Depositado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label text-muted small">Observación</label>
                        <textarea name="observacion" id="observacion" rows="2" class="form-control form-control-crm" placeholder="Observación o comentario..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Depósito</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Recaudación -->
<div class="modal fade" id="editRecaudacionModal" tabindex="-1" aria-labelledby="editRecaudacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=recaudaciones" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editRecaudacionModalLabel">Modificar Datos de Depósito</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_cash_register_id" class="form-label text-muted small">Caja de Origen *</label>
                        <select name="cash_register_id" id="edit_cash_register_id" class="form-select form-control-crm" required>
                            <?php foreach ($cajas as $cj): ?>
                                <option value="<?php echo $cj['id']; ?>">
                                    Caja #<?php echo $cj['id']; ?> - <?php echo htmlspecialchars($cj['employee_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_banco" class="form-label text-muted small">Entidad Bancaria *</label>
                        <select name="banco" id="edit_banco" class="form-select form-control-crm" required>
                            <option value="Banco Itaú">Banco Itaú</option>
                            <option value="Banco Familiar">Banco Familiar</option>
                            <option value="Banco Continental">Banco Continental</option>
                            <option value="Sudameris Bank">Sudameris Bank</option>
                            <option value="Banco Basa">Banco Basa</option>
                            <option value="Banco Atlas">Banco Atlas</option>
                            <option value="Banco GNB Paraguay">Banco GNB Paraguay</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nro_boleta" class="form-label text-muted small">Número de Boleta *</label>
                        <input type="text" name="nro_boleta" id="edit_nro_boleta" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_monto_depositar" class="form-label text-muted small">Monto a Depositar (Gs.) *</label>
                        <input type="number" name="monto_depositar" id="edit_monto_depositar" class="form-control form-control-crm" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_estado" class="form-label text-muted small">Estado de Depósito *</label>
                        <select name="estado" id="edit_estado" class="form-select form-control-crm" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="depositado">Depositado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_observacion" class="form-label text-muted small">Observación</label>
                        <textarea name="observacion" id="edit_observacion" rows="2" class="form-control form-control-crm"></textarea>
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
<div class="modal fade" id="deleteRecaudacionModal" tabindex="-1" aria-labelledby="deleteRecaudacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=recaudaciones" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Registro</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_recaudacion_name"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer.</p>
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
function openEditRecaudacionModal(id, register_id, monto, banco, boleta, observacion, estado) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_cash_register_id').value = register_id;
    document.getElementById('edit_monto_depositar').value = monto;
    document.getElementById('edit_banco').value = banco;
    document.getElementById('edit_nro_boleta').value = boleta;
    document.getElementById('edit_observacion').value = observacion;
    document.getElementById('edit_estado').value = estado;
    
    var editModal = new bootstrap.Modal(document.getElementById('editRecaudacionModal'));
    editModal.show();
}

function openDeleteRecaudacionModal(id, desc) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_recaudacion_name').textContent = desc;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteRecaudacionModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
