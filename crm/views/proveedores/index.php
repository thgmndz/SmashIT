<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Gestión de Proveedores</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createProveedorModal">
            <i class="fa fa-plus me-2"></i> Nuevo Proveedor
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

    <!-- Tabla de Proveedores -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>RUC</th>
                        <th>Teléfono</th>
                        <th>Categoría de Producto</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proveedores)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay proveedores registrados en el CRM.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proveedores as $prov): ?>
                            <tr>
                                <td>
                                    <div class="font-weight-semibold text-white"><?php echo htmlspecialchars($prov['nombre']); ?></div>
                                    <small class="text-muted">Registro: <?php echo date('d/m/Y', strtotime($prov['created_at'])); ?></small>
                                </td>
                                <td><span class="text-white"><?php echo htmlspecialchars($prov['ruc']); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($prov['telefono']); ?></span></td>
                                <td><span class="badge bg-primary px-3 rounded-pill"><?php echo htmlspecialchars($prov['categoria_producto']); ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditProveedorModal(
                                            '<?php echo $prov['id']; ?>',
                                            '<?php echo addslashes($prov['nombre']); ?>',
                                            '<?php echo addslashes($prov['ruc']); ?>',
                                            '<?php echo addslashes($prov['telefono']); ?>',
                                            '<?php echo addslashes($prov['categoria_producto']); ?>'
                                        )">
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                    <a href="print_purchase.php?type=proveedor&id=<?php echo $prov['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteProveedorModal(
                                            '<?php echo $prov['id']; ?>',
                                            '<?php echo addslashes($prov['nombre']); ?>'
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

<!-- Modal Registrar Proveedor -->
<div class="modal fade" id="createProveedorModal" tabindex="-1" aria-labelledby="createProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=proveedores" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createProveedorModalLabel">Registrar Nuevo Proveedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nombre" class="form-label text-muted small">Nombre *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-crm" placeholder="Ej. Distribuidora Alimentos S.A." required>
                    </div>
                    <div class="mb-3">
                        <label for="ruc" class="form-label text-muted small">RUC *</label>
                        <input type="text" name="ruc" id="ruc" class="form-control form-control-crm" placeholder="Ej. 1234567-1" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label text-muted small">Teléfono *</label>
                        <input type="text" name="telefono" id="telefono" class="form-control form-control-crm" placeholder="Ej. 0981-111-222" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoria_producto" class="form-label text-muted small">Categoría de Producto *</label>
                        <input type="text" name="categoria_producto" id="categoria_producto" class="form-control form-control-crm" placeholder="Ej. Carnes y Embutidos" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="editProveedorModal" tabindex="-1" aria-labelledby="editProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=proveedores" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editProveedorModalLabel">Modificar Datos de Proveedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label text-muted small">Nombre *</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ruc" class="form-label text-muted small">RUC *</label>
                        <input type="text" name="ruc" id="edit_ruc" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_telefono" class="form-label text-muted small">Teléfono *</label>
                        <input type="text" name="telefono" id="edit_telefono" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_categoria_producto" class="form-label text-muted small">Categoría de Producto *</label>
                        <input type="text" name="categoria_producto" id="edit_categoria_producto" class="form-control form-control-crm" required>
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
<div class="modal fade" id="deleteProveedorModal" tabindex="-1" aria-labelledby="deleteProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=proveedores" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Proveedor</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_proveedor_name"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer y eliminará los presupuestos asociados.</p>
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
function openEditProveedorModal(id, nombre, ruc, telefono, categoria) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_ruc').value = ruc;
    document.getElementById('edit_telefono').value = telefono;
    document.getElementById('edit_categoria_producto').value = categoria;
    
    var editModal = new bootstrap.Modal(document.getElementById('editProveedorModal'));
    editModal.show();
}

function openDeleteProveedorModal(id, nombre) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_proveedor_name').textContent = nombre;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteProveedorModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
