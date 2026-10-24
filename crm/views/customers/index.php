<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Gestión de Clientes</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
            <i class="fa fa-plus me-2"></i> Nuevo Cliente
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

    <!-- Tabla de Clientes -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>RUC / CI</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th class="text-center">Total Pedidos</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay clientes registrados en el CRM.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $cust): ?>
                            <tr>
                                <td>
                                    <div class="font-weight-semibold text-white"><?php echo htmlspecialchars($cust['name']); ?></div>
                                    <small class="text-muted">Registro: <?php echo date('d/m/Y', strtotime($cust['created_at'])); ?></small>
                                </td>
                                <td><span class="text-white"><?php echo htmlspecialchars($cust['ruc_ci'] ?: 'N/A'); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($cust['phone']); ?></span></td>
                                <td><?php echo htmlspecialchars($cust['email'] ?: 'No asignado'); ?></td>
                                <td><span class="text-truncate d-inline-block" style="max-width: 250px;"><?php echo htmlspecialchars($cust['address'] ?: 'N/A'); ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3"><?php echo (int)$cust['total_orders']; ?></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditCustomerModal(
                                            '<?php echo $cust['id']; ?>',
                                            '<?php echo addslashes($cust['name']); ?>',
                                            '<?php echo addslashes($cust['phone']); ?>',
                                            '<?php echo addslashes($cust['ruc_ci'] ?? ''); ?>',
                                            '<?php echo addslashes($cust['email']); ?>',
                                            '<?php echo addslashes($cust['address']); ?>',
                                            '<?php echo $cust['total_orders']; ?>'
                                        )">
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteCustomerModal(
                                            '<?php echo $cust['id']; ?>',
                                            '<?php echo addslashes($cust['name']); ?>'
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

<!-- Modal Registrar Cliente -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=customers" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createCustomerModalLabel">Registrar Nuevo Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted small">Nombre Completo *</label>
                        <input type="text" name="name" id="name" class="form-control form-control-crm" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label text-muted small">Teléfono / WhatsApp *</label>
                        <input type="text" name="phone" id="phone" class="form-control form-control-crm" placeholder="Ej. 0981 123 456" required>
                    </div>
                    <div class="mb-3">
                        <label for="ruc_ci" class="form-label text-muted small">RUC / C.I. (Opcional)</label>
                        <input type="text" name="ruc_ci" id="ruc_ci" class="form-control form-control-crm" placeholder="Ej. 1234567-8">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small">Correo Electrónico</label>
                        <input type="email" name="email" id="email" class="form-control form-control-crm" placeholder="Ej. juan@gmail.com">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label text-muted small">Dirección Residencial</label>
                        <textarea name="address" id="address" rows="3" class="form-control form-control-crm" placeholder="Ej. Av. Mariscal López 1234"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=customers" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editCustomerModalLabel">Modificar Datos de Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label text-muted small">Nombre Completo *</label>
                        <input type="text" name="name" id="edit_name" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label text-muted small">Teléfono / WhatsApp *</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ruc_ci" class="form-label text-muted small">RUC / C.I. (Opcional)</label>
                        <input type="text" name="ruc_ci" id="edit_ruc_ci" class="form-control form-control-crm">
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label text-muted small">Correo Electrónico</label>
                        <input type="email" name="email" id="edit_email" class="form-control form-control-crm">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label text-muted small">Dirección Residencial</label>
                        <textarea name="address" id="edit_address" rows="3" class="form-control form-control-crm"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_total_orders" class="form-label text-muted small">Total de Pedidos</label>
                        <input type="number" name="total_orders" id="edit_total_orders" min="0" class="form-control form-control-crm">
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
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=customers" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Cliente</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_customer_name"></h6>
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

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
