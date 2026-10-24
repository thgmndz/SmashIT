<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Órdenes de Compra</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createOrdenModal">
            <i class="fa fa-plus me-2"></i> Nueva Orden
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

    <!-- Tabla de Órdenes de Compra -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Presupuesto Vinculado</th>
                        <th>Proveedor</th>
                        <th>Sucursal</th>
                        <th>Funcionario</th>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Costo Unit.</th>
                        <th class="text-end">Total</th>
                        <th class="text-center" style="width: 180px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No hay órdenes de compra registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ordenes as $ord): ?>
                            <?php 
                                $total = $ord['cantidad'] * $ord['costo_unit'];
                            ?>
                            <tr>
                                <td><span class="text-white"><?php echo date('d/m/Y', strtotime($ord['fecha'])); ?></span></td>
                                <td>
                                    <?php if ($ord['presupuesto_compra_id']): ?>
                                        <span class="badge bg-info">Presp. #<?php echo $ord['presupuesto_compra_id']; ?> (<?php echo htmlspecialchars($ord['presupuesto_ref'] ?: 'Ref'); ?>)</span>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin presupuesto</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="text-white font-weight-semibold"><?php echo htmlspecialchars($ord['proveedor_nombre']); ?></span></td>
                                <td><span class="text-white"><?php echo htmlspecialchars($ord['sucursal']); ?></span></td>
                                <td><span class="text-muted"><?php echo htmlspecialchars($ord['funcionario_nombre'] ?: 'N/A'); ?></span></td>
                                <td>
                                    <span class="text-white"><?php echo htmlspecialchars($ord['producto']); ?></span>
                                    <?php if ($ord['observacion']): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($ord['observacion']); ?>">
                                            <?php echo htmlspecialchars($ord['observacion']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary"><?php echo (int)$ord['cantidad']; ?></span></td>
                                <td class="text-end text-white"><?php echo number_format($ord['costo_unit'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-end text-success font-weight-bold"><?php echo number_format($total, 0, ',', '.'); ?> Gs.</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info rounded-3 me-1"
                                        onclick="openEditOrdenModal(
                                            '<?php echo $ord['id']; ?>',
                                            '<?php echo addslashes($ord['referencia_presupuesto'] ?? ''); ?>',
                                            '<?php echo $ord['presupuesto_compra_id'] ?? ''; ?>',
                                            '<?php echo $ord['fecha']; ?>',
                                            '<?php echo addslashes($ord['sucursal']); ?>',
                                            '<?php echo $ord['proveedor_id']; ?>',
                                            '<?php echo $ord['funcionario_id']; ?>',
                                            '<?php echo addslashes($ord['observacion'] ?? ''); ?>',
                                            '<?php echo addslashes($ord['producto']); ?>',
                                            '<?php echo $ord['cantidad']; ?>',
                                            '<?php echo $ord['costo_unit']; ?>'
                                        )">
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                    <a href="print_purchase.php?type=orden&id=<?php echo $ord['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                        onclick="openDeleteOrdenModal(
                                            '<?php echo $ord['id']; ?>',
                                            '<?php echo 'Orden #' . $ord['id']; ?>'
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

<!-- Modal Registrar Orden -->
<div class="modal fade" id="createOrdenModal" tabindex="-1" aria-labelledby="createOrdenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=ordenes_compra" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createOrdenModalLabel">Registrar Orden de Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="referencia_presupuesto" class="form-label text-muted small">Referencia Presupuesto (Opcional)</label>
                            <input type="text" name="referencia_presupuesto" id="referencia_presupuesto" class="form-control form-control-crm" placeholder="Ej. PRES-001">
                        </div>
                        <div class="col-md-6">
                            <label for="presupuesto_compra_id" class="form-label text-muted small">Seleccione Presupuesto (Opcional)</label>
                            <select name="presupuesto_compra_id" id="presupuesto_compra_id" class="form-select form-control-crm" onchange="onPresupuestoSelected(this, 'create')">
                                <option value="">Ninguno / Seleccione</option>
                                <?php foreach ($presupuestos as $pres): ?>
                                    <option value="<?php echo $pres['id']; ?>" data-proveedor="<?php echo $pres['proveedor_id']; ?>" data-sucursal="<?php echo htmlspecialchars($pres['sucursal']); ?>" data-producto="<?php echo htmlspecialchars($pres['producto']); ?>" data-cantidad="<?php echo $pres['cantidad']; ?>" data-costo="<?php echo $pres['costo_unit']; ?>">
                                        Presp. #<?php echo $pres['id']; ?> (Ref: <?php echo htmlspecialchars($pres['referencia_pedido'] ?: 'N/A'); ?>) - <?php echo htmlspecialchars($pres['proveedor_nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha" class="form-label text-muted small">Fecha *</label>
                            <input type="date" name="fecha" id="fecha" class="form-control form-control-crm" value="2026-08-12" required>
                        </div>
                        <div class="col-md-6">
                            <label for="sucursal" class="form-label text-muted small">Sucursal *</label>
                            <select name="sucursal" id="sucursal" class="form-select form-control-crm" required>
                                <option value="">Seleccione Sucursal</option>
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
                        <div class="col-md-6">
                            <label for="proveedor_id" class="form-label text-muted small">Proveedor *</label>
                            <select name="proveedor_id" id="proveedor_id" class="form-select form-control-crm" required>
                                <option value="">Seleccione Proveedor</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="funcionario_id" class="form-label text-muted small">Funcionario *</label>
                            <select name="funcionario_id" id="funcionario_id" class="form-select form-control-crm" required>
                                <option value="">Seleccione Funcionario</option>
                                <?php foreach ($funcionarios as $func): ?>
                                    <option value="<?php echo $func['id']; ?>"><?php echo htmlspecialchars($func['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="producto" class="form-label text-muted small">Producto *</label>
                            <select name="producto" id="producto" class="form-select form-control-crm" required>
                                <option value="">Seleccione Producto</option>
                                <?php foreach ($products as $pr): ?>
                                    <option value="<?php echo htmlspecialchars($pr['name']); ?>"><?php echo htmlspecialchars($pr['name']); ?></option>
                                <?php endforeach; ?>
                                <option value="Pan Brioche">Pan Brioche</option>
                                <option value="Papas Fritas Congeladas 10kg">Papas Fritas Congeladas 10kg</option>
                                <option value="Coca Cola 1.5L">Coca Cola 1.5L</option>
                                <option value="Salsa Cheddar Premium">Salsa Cheddar Premium</option>
                                <option value="Caja Hamburguesa Mediana">Caja Hamburguesa Mediana</option>
                                <option value="Queso Cheddar Fetas">Queso Cheddar Fetas</option>
                                <option value="Lechuga Repollada">Lechuga Repollada</option>
                                <option value="Medallón de Carne Vacuna">Medallón de Carne Vacuna</option>
                                <option value="Aceite de Girasol 20L">Aceite de Girasol 20L</option>
                                <option value="Detergente Industrial">Detergente Industrial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cantidad" class="form-label text-muted small">Cantidad *</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control form-control-crm" min="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="costo_unit" class="form-label text-muted small">Costo Unit. (Gs.) *</label>
                            <input type="number" name="costo_unit" id="costo_unit" class="form-control form-control-crm" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="observacion" class="form-label text-muted small">Observación</label>
                            <textarea name="observacion" id="observacion" rows="1" class="form-control form-control-crm" placeholder="Observaciones de la orden..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-crm-primary rounded-3">Guardar Orden</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Orden -->
<div class="modal fade" id="editOrdenModal" tabindex="-1" aria-labelledby="editOrdenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=ordenes_compra" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="editOrdenModalLabel">Modificar Orden de Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_referencia_presupuesto" class="form-label text-muted small">Referencia Presupuesto (Opcional)</label>
                            <input type="text" name="referencia_presupuesto" id="edit_referencia_presupuesto" class="form-control form-control-crm">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_presupuesto_compra_id" class="form-label text-muted small">Seleccione Presupuesto (Opcional)</label>
                            <select name="presupuesto_compra_id" id="edit_presupuesto_compra_id" class="form-select form-control-crm" onchange="onPresupuestoSelected(this, 'edit')">
                                <option value="">Ninguno / Seleccione</option>
                                <?php foreach ($presupuestos as $pres): ?>
                                    <option value="<?php echo $pres['id']; ?>" data-proveedor="<?php echo $pres['proveedor_id']; ?>" data-sucursal="<?php echo htmlspecialchars($pres['sucursal']); ?>" data-producto="<?php echo htmlspecialchars($pres['producto']); ?>" data-cantidad="<?php echo $pres['cantidad']; ?>" data-costo="<?php echo $pres['costo_unit']; ?>">
                                        Presp. #<?php echo $pres['id']; ?> (Ref: <?php echo htmlspecialchars($pres['referencia_pedido'] ?: 'N/A'); ?>) - <?php echo htmlspecialchars($pres['proveedor_nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_fecha" class="form-label text-muted small">Fecha *</label>
                            <input type="date" name="fecha" id="edit_fecha" class="form-control form-control-crm" required>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label for="edit_proveedor_id" class="form-label text-muted small">Proveedor *</label>
                            <select name="proveedor_id" id="edit_proveedor_id" class="form-select form-control-crm" required>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_funcionario_id" class="form-label text-muted small">Funcionario *</label>
                            <select name="funcionario_id" id="edit_funcionario_id" class="form-select form-control-crm" required>
                                <?php foreach ($funcionarios as $func): ?>
                                    <option value="<?php echo $func['id']; ?>"><?php echo htmlspecialchars($func['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_producto" class="form-label text-muted small">Producto *</label>
                            <select name="producto" id="edit_producto" class="form-select form-control-crm" required>
                                <?php foreach ($products as $pr): ?>
                                    <option value="<?php echo htmlspecialchars($pr['name']); ?>"><?php echo htmlspecialchars($pr['name']); ?></option>
                                <?php endforeach; ?>
                                <option value="Pan Brioche">Pan Brioche</option>
                                <option value="Papas Fritas Congeladas 10kg">Papas Fritas Congeladas 10kg</option>
                                <option value="Coca Cola 1.5L">Coca Cola 1.5L</option>
                                <option value="Salsa Cheddar Premium">Salsa Cheddar Premium</option>
                                <option value="Caja Hamburguesa Mediana">Caja Hamburguesa Mediana</option>
                                <option value="Queso Cheddar Fetas">Queso Cheddar Fetas</option>
                                <option value="Lechuga Repollada">Lechuga Repollada</option>
                                <option value="Medallón de Carne Vacuna">Medallón de Carne Vacuna</option>
                                <option value="Aceite de Girasol 20L">Aceite de Girasol 20L</option>
                                <option value="Detergente Industrial">Detergente Industrial</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_cantidad" class="form-label text-muted small">Cantidad *</label>
                            <input type="number" name="cantidad" id="edit_cantidad" class="form-control form-control-crm" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_costo_unit" class="form-label text-muted small">Costo Unit. (Gs.) *</label>
                            <input type="number" name="costo_unit" id="edit_costo_unit" class="form-control form-control-crm" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_observacion" class="form-label text-muted small">Observación</label>
                            <textarea name="observacion" id="edit_observacion" rows="1" class="form-control form-control-crm"></textarea>
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
<div class="modal fade" id="deleteOrdenModal" tabindex="-1" aria-labelledby="deleteOrdenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=ordenes_compra" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-trash-alt me-2"></i> Eliminar Orden</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas eliminar permanentemente a:</p>
                    <h6 class="text-white my-3" id="delete_orden_name"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer y desvinculará las facturas de compra asociadas.</p>
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
function onPresupuestoSelected(selectElement, type) {
    var selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption.value === "") {
        return;
    }
    
    var provId = selectedOption.getAttribute('data-proveedor');
    var sucursal = selectedOption.getAttribute('data-sucursal');
    var producto = selectedOption.getAttribute('data-producto');
    var cantidad = selectedOption.getAttribute('data-cantidad');
    var costo = selectedOption.getAttribute('data-costo');
    
    if (type === 'create') {
        document.getElementById('proveedor_id').value = provId;
        document.getElementById('sucursal').value = sucursal;
        document.getElementById('cantidad').value = cantidad;
        document.getElementById('costo_unit').value = costo;
        
        var productSelect = document.getElementById('producto');
        var found = false;
        for (var i = 0; i < productSelect.options.length; i++) {
            if (productSelect.options[i].value === producto) {
                productSelect.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            var newOpt = document.createElement('option');
            newOpt.value = producto;
            newOpt.text = producto;
            productSelect.appendChild(newOpt);
            productSelect.value = producto;
        }
    } else {
        document.getElementById('edit_proveedor_id').value = provId;
        document.getElementById('edit_sucursal').value = sucursal;
        document.getElementById('edit_cantidad').value = cantidad;
        document.getElementById('edit_costo_unit').value = costo;
        
        var productSelect = document.getElementById('edit_producto');
        var found = false;
        for (var i = 0; i < productSelect.options.length; i++) {
            if (productSelect.options[i].value === producto) {
                productSelect.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            var newOpt = document.createElement('option');
            newOpt.value = producto;
            newOpt.text = producto;
            productSelect.appendChild(newOpt);
            productSelect.value = producto;
        }
    }
}

function openEditOrdenModal(id, referencia, presupuesto_id, fecha, sucursal, proveedor_id, funcionario_id, observacion, producto, cantidad, costo) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_referencia_presupuesto').value = referencia;
    document.getElementById('edit_presupuesto_compra_id').value = presupuesto_id;
    document.getElementById('edit_fecha').value = fecha;
    document.getElementById('edit_sucursal').value = sucursal;
    document.getElementById('edit_proveedor_id').value = proveedor_id;
    document.getElementById('edit_funcionario_id').value = funcionario_id;
    document.getElementById('edit_observacion').value = observacion;
    document.getElementById('edit_cantidad').value = cantidad;
    document.getElementById('edit_costo_unit').value = costo;
    
    var productSelect = document.getElementById('edit_producto');
    var found = false;
    for (var i = 0; i < productSelect.options.length; i++) {
        if (productSelect.options[i].value === producto) {
            productSelect.selectedIndex = i;
            found = true;
            break;
        }
    }
    if (!found) {
        var newOpt = document.createElement('option');
        newOpt.value = producto;
        newOpt.text = producto;
        productSelect.appendChild(newOpt);
        productSelect.value = producto;
    }
    
    var editModal = new bootstrap.Modal(document.getElementById('editOrdenModal'));
    editModal.show();
}

function openDeleteOrdenModal(id, desc) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_orden_name').textContent = desc;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteOrdenModal'));
    deleteModal.show();
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
