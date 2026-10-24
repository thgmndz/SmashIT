<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Gestionar Mesas del Local</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createTableModal">
            <i class="fa fa-plus me-2"></i> Nueva Mesa
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

    <!-- Grid de Mesas -->
    <div class="row g-4 mb-5">
        <?php if (empty($tables)): ?>
            <div class="col-12 text-center text-muted py-5 glass-panel">
                <i class="fa fa-table fa-3x mb-3 text-muted"></i>
                <p class="mb-0">No hay mesas registradas en el local.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tables as $table): 
                $statusColor = 'border-success text-success';
                $statusLabel = 'Libre';
                $statusIcon = 'fa-check-circle';
                $bgSoft = 'rgba(40, 167, 69, 0.08)';

                if ($table['status'] === 'ocupado') {
                    $statusColor = 'border-danger text-danger';
                    $statusLabel = 'Ocupado';
                    $statusIcon = 'fa-user-friends';
                    $bgSoft = 'rgba(220, 53, 69, 0.08)';
                } elseif ($table['status'] === 'reservado') {
                    $statusColor = 'border-warning text-warning';
                    $statusLabel = 'Reservado';
                    $statusIcon = 'fa-calendar-alt';
                    $bgSoft = 'rgba(255, 193, 7, 0.08)';
                }
            ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="glass-panel p-4 rounded-4 text-center border-top border-4 <?php echo $statusColor; ?> stat-card" style="background-color: <?php echo $bgSoft; ?>;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-secondary">Cap. <?php echo (int)$table['capacity']; ?> per.</span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="openDeleteTableModal('<?php echo $table['id']; ?>', '<?php echo $table['table_number']; ?>')">
                                <i class="fa fa-trash-alt"></i>
                            </button>
                        </div>
                        
                        <div class="my-3">
                            <h2 class="display-5 text-white font-weight-bold mb-0">MESA <?php echo (int)$table['table_number']; ?></h2>
                            <p class="small mb-0 mt-1 d-flex align-items-center justify-content-center fw-semibold text-uppercase">
                                <i class="fa <?php echo $statusIcon; ?> me-2"></i> <?php echo $statusLabel; ?>
                            </p>
                        </div>

                        <?php if ($table['status'] === 'reservado' && !empty($table['reservation_name'])): ?>
                            <div class="bg-dark bg-opacity-50 py-2 px-3 rounded-3 mb-3 text-xs text-white-50 text-start">
                                <div><i class="fa fa-user text-warning me-1"></i> <?php echo htmlspecialchars($table['reservation_name']); ?></div>
                                <div><i class="fa fa-id-card text-warning me-1"></i> CI: <?php echo htmlspecialchars($table['reservation_ci']); ?></div>
                                <div><i class="fa fa-clock text-warning me-1"></i> <?php echo date('H:i', strtotime($table['reservation_datetime'])); ?> hs</div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($table['reservation_name'])): ?>
                            <button type="button" class="btn btn-sm btn-dark border border-secondary border-opacity-25 w-100 rounded-3 mb-2"
                                onclick="showReservationDetails(
                                    '<?php echo (int)$table['table_number']; ?>',
                                    '<?php echo addslashes($table['reservation_name']); ?>',
                                    '<?php echo addslashes($table['reservation_ci']); ?>',
                                    '<?php echo date('d/m/Y H:i', strtotime($table['reservation_datetime'])); ?>'
                                )">
                                <i class="fa fa-calendar-alt text-warning me-1"></i> Ver Reserva
                            </button>
                        <?php endif; ?>

                        <?php if (!empty($table['current_order_code'])): ?>
                            <div class="bg-dark bg-opacity-50 py-1.5 px-3 rounded-3 mb-3 text-xs text-white-50">
                                Pedido: <strong><?php echo htmlspecialchars($table['current_order_code']); ?></strong>
                            </div>
                        <?php elseif ($table['status'] !== 'reservado'): ?>
                            <div class="py-1.5 mb-3 text-xs text-muted">
                                Sin pedido activo
                            </div>
                        <?php endif; ?>

                        <?php if ($table['status'] === 'ocupado'): ?>
                            <button type="button" class="btn btn-sm btn-crm-primary w-100 rounded-3 mb-2"
                                onclick="openTakeOrderModal('<?php echo $table['id']; ?>', '<?php echo $table['table_number']; ?>')">
                                <i class="fa fa-receipt me-1"></i> Tomar Pedido
                            </button>
                        <?php endif; ?>

                        <?php 
                        $resDate = '';
                        $resTime = '';
                        if (!empty($table['reservation_datetime'])) {
                            $resDate = date('Y-m-d', strtotime($table['reservation_datetime']));
                            $resTime = date('H:i', strtotime($table['reservation_datetime']));
                        }
                        ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 rounded-3" 
                            onclick="openEditTableModal(
                                '<?php echo $table['id']; ?>',
                                '<?php echo $table['table_number']; ?>',
                                '<?php echo $table['db_status']; ?>',
                                '<?php echo htmlspecialchars($table['current_order_code'] ?? ''); ?>',
                                '<?php echo addslashes($table['reservation_name'] ?? ''); ?>',
                                '<?php echo addslashes($table['reservation_ci'] ?? ''); ?>',
                                '<?php echo $resDate; ?>',
                                '<?php echo $resTime; ?>'
                            )">
                            <i class="fa fa-sync-alt me-1"></i> Cambiar Estado
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nueva Mesa -->
<div class="modal fade" id="createTableModal" tabindex="-1" aria-labelledby="createTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=tables" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createTableModalLabel">Registrar Mesa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="table_number" class="form-label text-muted small">Número de Mesa *</label>
                        <input type="number" name="table_number" id="table_number" min="1" class="form-control form-control-crm" required>
                    </div>
                    <div class="mb-3">
                        <label for="capacity" class="form-label text-muted small">Capacidad (Personas)</label>
                        <input type="number" name="capacity" id="capacity" min="1" max="20" value="4" class="form-control form-control-crm" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado de Mesa -->
<div class="modal fade" id="editTableModal" tabindex="-1" aria-labelledby="editTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=tables" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="table_edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h6 class="modal-title font-weight-bold" id="editTableModalLabel">Estado: Mesa <span id="table_edit_number" class="text-warning"></span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="table_edit_status" class="form-label text-muted small">Estado Actual</label>
                        <select name="status" id="table_edit_status" class="form-select form-control-crm" onchange="toggleReservationFields()">
                            <option value="libre">LIBRE ✅</option>
                            <option value="ocupado">OCUPADO ❌</option>
                            <option value="reservado">RESERVADO ⏳</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="table_edit_order_code" class="form-label text-muted small">Código de Pedido Asociado</label>
                        <input type="text" name="current_order_code" id="table_edit_order_code" class="form-control form-control-crm" placeholder="Ej. SMASH-78A1">
                        <div class="form-text text-muted">Opcional para relacionar una comanda activa.</div>
                    </div>
                    
                    <div id="reservationFields" style="display: none; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 15px;">
                        <div class="mb-3">
                            <label for="table_edit_res_name" class="form-label text-muted small">Nombre del Cliente *</label>
                            <input type="text" name="reservation_name" id="table_edit_res_name" class="form-control form-control-crm" placeholder="Ej. Carlos Bogado">
                        </div>
                        <div class="mb-3">
                            <label for="table_edit_res_ci" class="form-label text-muted small">Cédula del Cliente *</label>
                            <input type="text" name="reservation_ci" id="table_edit_res_ci" class="form-control form-control-crm" placeholder="Ej. 1234567">
                        </div>
                        <div class="mb-3">
                            <label for="table_edit_res_date" class="form-label text-muted small">Fecha Reserva *</label>
                            <input type="date" name="reservation_date" id="table_edit_res_date" class="form-control form-control-crm">
                        </div>
                        <div class="mb-3">
                            <label for="table_edit_res_time" class="form-label text-muted small">Hora Reserva *</label>
                            <input type="time" name="reservation_time" id="table_edit_res_time" class="form-control form-control-crm">
                        </div>
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

<!-- Modal Eliminar Mesa -->
<div class="modal fade" id="deleteTableModal" tabindex="-1" aria-labelledby="deleteTableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=tables" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_table_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Quitar Mesa</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar la</p>
                    <h6 class="text-white my-3">Mesa Nro. <span id="delete_table_number"></span></h6>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 rounded-3">Quitar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tomar Pedido -->
<div class="modal fade" id="takeOrderModal" tabindex="-1" aria-labelledby="takeOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=tables" method="POST">
                <input type="hidden" name="action" value="take_order">
                <input type="hidden" name="table_id" id="take_order_table_id">
                <input type="hidden" name="table_number" id="take_order_table_number_val">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="takeOrderModalLabel">Tomar Pedido - Mesa <span id="take_order_table_number" class="text-warning"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label for="take_order_customer_select" class="form-label text-muted small">Seleccionar Cliente (Existente)</label>
                            <select name="customer_id" id="take_order_customer_select" class="form-select form-control-crm" onchange="handleCustomerSelectChange()">
                                <option value="new">-- Registrar Manualmente --</option>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $cust): ?>
                                        <option value="<?php echo $cust['id']; ?>" data-name="<?php echo htmlspecialchars($cust['name']); ?>" data-phone="<?php echo htmlspecialchars($cust['phone']); ?>" data-ruc="<?php echo htmlspecialchars($cust['ruc_ci'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($cust['name'] . ' (' . $cust['phone'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="take_order_customer_name" class="form-label text-muted small">Nombre del Cliente *</label>
                            <input type="text" name="customer_name" id="take_order_customer_name" class="form-control form-control-crm" placeholder="Ej. Juan Pérez" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="take_order_customer_phone" class="form-label text-muted small">Teléfono / Celular *</label>
                            <input type="text" name="customer_phone" id="take_order_customer_phone" class="form-control form-control-crm" placeholder="Ej. 0981123456" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="take_order_ruc" class="form-label text-muted small">RUC / C.I. (Opcional)</label>
                            <input type="text" name="ruc" id="take_order_ruc" class="form-control form-control-crm" placeholder="Ej. 1234567-8">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="take_order_payment" class="form-label text-muted small">Método de Pago Seleccionado *</label>
                            <select name="payment_method" id="take_order_payment" class="form-select form-control-crm" required>
                                <option value="Efectivo">Efectivo 💵</option>
                                <option value="Tarjeta">Tarjeta POS 💳</option>
                                <option value="Transferencia">Transferencia / Billetera 📱</option>
                            </select>
                        </div>
                    </div>

                    <script>
                    function handleCustomerSelectChange() {
                        const select = document.getElementById('take_order_customer_select');
                        const selectedOption = select.options[select.selectedIndex];
                        const nameInput = document.getElementById('take_order_customer_name');
                        const phoneInput = document.getElementById('take_order_customer_phone');
                        const rucInput = document.getElementById('take_order_ruc');
                        
                        if (select.value === 'new') {
                            nameInput.value = '';
                            phoneInput.value = '';
                            rucInput.value = '';
                            nameInput.readOnly = false;
                            phoneInput.readOnly = false;
                            rucInput.readOnly = false;
                            phoneInput.required = true;
                        } else {
                            nameInput.value = selectedOption.getAttribute('data-name');
                            phoneInput.value = selectedOption.getAttribute('data-phone');
                            rucInput.value = selectedOption.getAttribute('data-ruc') || '';
                            nameInput.readOnly = true;
                            phoneInput.readOnly = true;
                            rucInput.readOnly = true;
                            phoneInput.required = false;
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const searchInput = document.getElementById('product_search');
                        if (searchInput) {
                            searchInput.addEventListener('input', function() {
                                const query = this.value.toLowerCase().trim();
                                const rows = document.querySelectorAll('#takeOrderModal table tbody tr');
                                rows.forEach(row => {
                                    const productName = row.querySelector('td:first-child').textContent.toLowerCase();
                                    const categoryName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                                    if (productName.includes(query) || categoryName.includes(query)) {
                                        row.style.display = '';
                                    } else {
                                        row.style.display = 'none';
                                    }
                                });
                            });
                        }
                    });
                    </script>

                    <h6 class="text-muted text-uppercase small mb-3">Seleccionar Productos</h6>
                    <div class="mb-3">
                        <label for="product_search" class="form-label text-muted small">Buscar Producto por Nombre o Categoría</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa fa-search"></i></span>
                            <input type="text" id="product_search" class="form-control form-control-crm" placeholder="Escriba aquí para buscar producto...">
                        </div>
                    </div>
                    <div class="table-responsive border border-secondary border-opacity-25 rounded-3" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-dark table-striped table-hover custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th class="text-center" style="width: 120px;">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $prod): ?>
                                    <?php if ($prod['is_available']): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-semibold text-white"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($prod['category_name']); ?></span></td>
                                            <td><strong><?php echo number_format($prod['base_price'], 0, ',', '.'); ?> Gs.</strong></td>
                                            <td>
                                                <input type="number" name="quantities[<?php echo $prod['id']; ?>]" min="0" max="20" value="0" class="form-control form-control-crm text-center py-1" placeholder="0" aria-label="Cantidad">
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Confirmar y Enviar Pedido 🚀</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalles de Reserva -->
<div class="modal fade" id="reservationDetailsModal" tabindex="-1" aria-labelledby="reservationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title font-weight-bold" id="reservationDetailsModalLabel">Reserva: Mesa <span id="res_detail_table_number" class="text-warning"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label text-muted small d-block">Nombre del Cliente</label>
                    <div class="fw-semibold fs-6" id="res_detail_name"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small d-block">Documento / C.I.</label>
                    <div class="fw-semibold fs-6" id="res_detail_ci"></div>
                </div>
                <div class="mb-0">
                    <label class="form-label text-muted small d-block">Fecha y Hora</label>
                    <div class="fw-semibold text-warning fs-6" id="res_detail_datetime"></div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function showReservationDetails(tableNum, name, ci, datetime) {
    document.getElementById('res_detail_table_number').textContent = tableNum;
    document.getElementById('res_detail_name').textContent = name;
    document.getElementById('res_detail_ci').textContent = ci;
    document.getElementById('res_detail_datetime').textContent = datetime + ' hs';

    const modal = new bootstrap.Modal(document.getElementById('reservationDetailsModal'));
    modal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
