<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Arqueo de Caja</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createArqueoModal">
            <i class="fa fa-calculator me-2"></i> Nuevo Arqueo de Caja
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

    <!-- Tabla de Arqueos -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID Arqueo</th>
                        <th>Caja Vinc.</th>
                        <th>Funcionario (Cajero)</th>
                        <th>Fecha Arqueo</th>
                        <th class="text-end">Efectivo</th>
                        <th class="text-end">Tarjeta</th>
                        <th class="text-end">Cheque</th>
                        <th class="text-end">Monto Sistema</th>
                        <th class="text-end">Diferencia</th>
                        <th>Observación</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($arqueos)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No hay registros de arqueos en el sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($arqueos as $aq): ?>
                            <?php 
                                $declarado = $aq['efectivo_declarado'] + $aq['tarjeta_declarado'] + $aq['cheque_declarado'];
                            ?>
                            <tr>
                                <td><span class="text-white font-weight-bold">Arqueo #<?php echo $aq['id']; ?></span></td>
                                <td><span class="badge bg-info">Caja #<?php echo $aq['cash_register_id']; ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($aq['funcionario_nombre'] ?: 'N/A'); ?></span></td>
                                <td><span class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($aq['fecha'])); ?></span></td>
                                <td class="text-end text-white"><?php echo number_format($aq['efectivo_declarado'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-end text-white"><?php echo number_format($aq['tarjeta_declarado'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-end text-white"><?php echo number_format($aq['cheque_declarado'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-end text-white"><?php echo number_format($aq['monto_sistema'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-end font-weight-bold <?php echo $aq['diferencia'] < 0 ? 'text-danger' : ($aq['diferencia'] > 0 ? 'text-warning' : 'text-success'); ?>">
                                    <?php echo number_format($aq['diferencia'], 0, ',', '.'); ?> Gs.
                                </td>
                                <td><span class="text-muted text-truncate d-inline-block" style="max-width: 150px;"><?php echo htmlspecialchars($aq['observacion'] ?: 'N/A'); ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditArqueoModal(
                                            '<?php echo $aq['id']; ?>',
                                            '<?php echo $aq['cash_register_id']; ?>',
                                            '<?php echo $aq['efectivo_declarado']; ?>',
                                            '<?php echo $aq['tarjeta_declarado']; ?>',
                                            '<?php echo $aq['cheque_declarado']; ?>',
                                            '<?php echo $aq['monto_sistema']; ?>',
                                            '<?php echo addslashes($aq['observacion'] ?? ''); ?>'
                                        )">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a href="print_purchase.php?type=arqueo&id=<?php echo $aq['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteArqueoModal(
                                            '<?php echo $aq['id']; ?>',
                                            'Arqueo #<?php echo $aq['id']; ?>'
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

<!-- Modal Registrar Arqueo -->
<div class="modal fade" id="createArqueoModal" tabindex="-1" aria-labelledby="createArqueoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=arqueos" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createArqueoModalLabel">Procesar Arqueo de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cash_register_id" class="form-label text-muted small">Caja a Arquear *</label>
                            <select name="cash_register_id" id="cash_register_id" class="form-select form-control-crm" required>
                                <option value="">Seleccione Caja</option>
                                <?php foreach ($cajas as $cj): ?>
                                    <option value="<?php echo $cj['id']; ?>">
                                        Caja #<?php echo $cj['id']; ?> - <?php echo htmlspecialchars($cj['employee_name']); ?> (<?php echo date('d/m/Y', strtotime($cj['opened_at'])); ?>) - [<?php echo $cj['status'] === 'open' ? 'ABIERTA' : 'CERRADA'; ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="monto_sistema" class="form-label text-muted small">Monto Teórico Sistema (Gs.) *</label>
                            <input type="number" name="monto_sistema" id="monto_sistema" class="form-control form-control-crm" min="0" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="efectivo_declarado" class="form-label text-muted small">Efectivo Declarado (Gs.) *</label>
                            <input type="number" name="efectivo_declarado" id="efectivo_declarado" class="form-control form-control-crm" min="0" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tarjeta_declarado" class="form-label text-muted small">Tarjeta Declarado (Gs.) *</label>
                            <input type="number" name="tarjeta_declarado" id="tarjeta_declarado" class="form-control form-control-crm" min="0" value="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cheque_declarado" class="form-label text-muted small">Cheques Declarado (Gs.) *</label>
                            <input type="number" name="cheque_declarado" id="cheque_declarado" class="form-control form-control-crm" min="0" value="0" required>
                        </div>
                        <div class="col-12">
                            <label for="observacion" class="form-label text-muted small">Observación</label>
                            <textarea name="observacion" id="observacion" rows="3" class="form-control form-control-crm" placeholder="Observaciones o notas sobre el arqueo..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Arqueo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Arqueo -->
<div class="modal fade" id="editArqueoModal" tabindex="-1" aria-labelledby="editArqueoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=arqueos" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editArqueoModalLabel">Modificar Registro de Arqueo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_cash_register_id" class="form-label text-muted small">Caja Asociada *</label>
                            <select name="cash_register_id" id="edit_cash_register_id" class="form-select form-control-crm" required>
                                <?php foreach ($cajas as $cj): ?>
                                    <option value="<?php echo $cj['id']; ?>">
                                        Caja #<?php echo $cj['id']; ?> - <?php echo htmlspecialchars($cj['employee_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_monto_sistema" class="form-label text-muted small">Monto Teórico Sistema (Gs.) *</label>
                            <input type="number" name="monto_sistema" id="edit_monto_sistema" class="form-control form-control-crm" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_efectivo_declarado" class="form-label text-muted small">Efectivo Declarado (Gs.) *</label>
                            <input type="number" name="efectivo_declarado" id="edit_efectivo_declarado" class="form-control form-control-crm" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_tarjeta_declarado" class="form-label text-muted small">Tarjeta Declarado (Gs.) *</label>
                            <input type="number" name="tarjeta_declarado" id="edit_tarjeta_declarado" class="form-control form-control-crm" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_cheque_declarado" class="form-label text-muted small">Cheques Declarado (Gs.) *</label>
                            <input type="number" name="cheque_declarado" id="edit_cheque_declarado" class="form-control form-control-crm" min="0" required>
                        </div>
                        <div class="col-12">
                            <label for="edit_observacion" class="form-label text-muted small">Observación</label>
                            <textarea name="observacion" id="edit_observacion" rows="3" class="form-control form-control-crm"></textarea>
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
<div class="modal fade" id="deleteArqueoModal" tabindex="-1" aria-labelledby="deleteArqueoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=arqueos" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Arqueo</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_arqueo_name"></h6>
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
function openEditArqueoModal(id, register_id, efectivo, tarjeta, cheque, sistema, observacion) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_cash_register_id').value = register_id;
    document.getElementById('edit_efectivo_declarado').value = efectivo;
    document.getElementById('edit_tarjeta_declarado').value = tarjeta;
    document.getElementById('edit_cheque_declarado').value = cheque;
    document.getElementById('edit_monto_sistema').value = sistema;
    document.getElementById('edit_observacion').value = observacion;
    
    var editModal = new bootstrap.Modal(document.getElementById('editArqueoModal'));
    editModal.show();
}

function openDeleteArqueoModal(id, desc) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_arqueo_name').textContent = desc;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteArqueoModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
