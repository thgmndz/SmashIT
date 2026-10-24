<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Choferes de Delivery</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createDriverModal">
            <i class="fa fa-plus me-2"></i> Registrar Nuevo Chofer
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

    <!-- Tabla de Choferes -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID Chofer</th>
                        <th>Nombre y Apellido</th>
                        <th>Teléfono</th>
                        <th>Vehículo</th>
                        <th>Chapa / Patente</th>
                        <th class="text-center">Estado</th>
                        <th>Registro</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($drivers)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay choferes registrados en el sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($drivers as $dr): ?>
                            <tr>
                                <td><span class="text-white font-weight-bold">#<?php echo $dr['id']; ?></span></td>
                                <td><span class="text-white font-weight-semibold"><?php echo htmlspecialchars($dr['nombre']); ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($dr['telefono']); ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($dr['vehiculo']); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($dr['chapa']); ?></span></td>
                                <td class="text-center">
                                    <span class="badge <?php echo $dr['activo'] ? 'bg-success' : 'bg-danger'; ?> px-2 py-1">
                                        <?php echo $dr['activo'] ? 'ACTIVO' : 'INACTIVO'; ?>
                                    </span>
                                </td>
                                <td><span class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($dr['created_at'])); ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditDriverModal(
                                            '<?php echo $dr['id']; ?>',
                                            '<?php echo addslashes($dr['nombre']); ?>',
                                            '<?php echo addslashes($dr['telefono']); ?>',
                                            '<?php echo addslashes($dr['vehiculo']); ?>',
                                            '<?php echo addslashes($dr['chapa']); ?>',
                                            '<?php echo $dr['activo']; ?>'
                                        )">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteDriverModal(
                                            '<?php echo $dr['id']; ?>',
                                            '<?php echo addslashes($dr['nombre']); ?>'
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

<!-- Modal Registrar Chofer -->
<div class="modal fade" id="createDriverModal" tabindex="-1" aria-labelledby="createDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=delivery_drivers" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createDriverModalLabel">Registrar Nuevo Chofer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nombre" class="form-label text-muted small">Nombre y Apellido *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label text-muted small">Teléfono de Contacto *</label>
                        <input type="text" name="telefono" id="telefono" class="form-control form-control-crm" placeholder="Ej. 0981-123456" required>
                    </div>
                    <div class="mb-3">
                        <label for="vehiculo" class="form-label text-muted small">Vehículo (Moto / Furgón) *</label>
                        <input type="text" name="vehiculo" id="vehiculo" class="form-control form-control-crm" placeholder="Ej. Motocicleta Kenton" required>
                    </div>
                    <div class="mb-3">
                        <label for="chapa" class="form-label text-muted small">Chapa / Patente *</label>
                        <input type="text" name="chapa" id="chapa" class="form-control form-control-crm" placeholder="Ej. 123-AAA" required>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" checked value="1">
                        <label class="form-check-label text-white small" for="activo">Chofer Activo para Envíos</label>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Chofer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Chofer -->
<div class="modal fade" id="editDriverModal" tabindex="-1" aria-labelledby="editDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=delivery_drivers" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editDriverModalLabel">Modificar Datos de Chofer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label text-muted small">Nombre y Apellido *</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_telefono" class="form-label text-muted small">Teléfono de Contacto *</label>
                        <input type="text" name="telefono" id="edit_telefono" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vehiculo" class="form-label text-muted small">Vehículo (Moto / Furgón) *</label>
                        <input type="text" name="vehiculo" id="edit_vehiculo" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_chapa" class="form-label text-muted small">Chapa / Patente *</label>
                        <input type="text" name="chapa" id="edit_chapa" class="form-control form-control-crm" required>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="edit_activo" value="1">
                        <label class="form-check-label text-white small" for="edit_activo">Chofer Activo para Envíos</label>
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
<div class="modal fade" id="deleteDriverModal" tabindex="-1" aria-labelledby="deleteDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=delivery_drivers" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Chofer</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_driver_name"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción desvinculará al chofer de los pedidos anteriores.</p>
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
function openEditDriverModal(id, nombre, telefono, vehiculo, chapa, activo) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_telefono').value = telefono;
    document.getElementById('edit_vehiculo').value = vehiculo;
    document.getElementById('edit_chapa').value = chapa;
    document.getElementById('edit_activo').checked = (activo == 1);
    
    var editModal = new bootstrap.Modal(document.getElementById('editDriverModal'));
    editModal.show();
}

function openDeleteDriverModal(id, desc) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_driver_name').textContent = desc;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteDriverModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
