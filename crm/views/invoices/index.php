<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Facturación (Normas SET)</h2>
        <button type="button" class="btn btn-crm-primary rounded-3" data-bs-toggle="modal" data-bs-target="#createManualInvoiceModal">
            <i class="fa fa-plus me-2"></i> Nueva Factura
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

    <!-- Listado de Facturas -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nro. Factura</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Timbrado</th>
                        <th>Condición</th>
                        <th class="text-end">Total Facturado</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No se han emitido facturas de venta.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): 
                            if ($inv['status'] === 'emitida') {
                                $statusClass = 'bg-success';
                            } elseif ($inv['status'] === 'pendiente') {
                                $statusClass = 'bg-warning text-dark';
                            } else {
                                $statusClass = 'bg-danger';
                            }
                            
                            // Obtener detalles de ítems de la factura
                            $db = CRMDatabase::getConnection();
                            $stmtItems = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
                            $stmtItems->execute([':id' => $inv['id']]);
                            $items = $stmtItems->fetchAll();
                            $itemsJson = json_encode($items);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?></td>
                                <td><div class="font-weight-semibold text-white"><?php echo htmlspecialchars($inv['customer_name']); ?></div></td>
                                <td>
                                    <div>Nro: <?php echo htmlspecialchars($inv['timbrado_no']); ?></div>
                                    <small class="text-muted">Vence: <?php echo date('d/m/Y', strtotime($inv['timbrado_expiry'])); ?></small>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($inv['condition']); ?></span></td>
                                <td class="text-end font-weight-bold text-warning"><?php echo number_format($inv['total_amount'], 0, ',', '.'); ?> Gs.</td>
                                <td class="text-center">
                                    <span class="badge <?php echo $statusClass; ?> text-uppercase">
                                        <?php echo htmlspecialchars($inv['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-dark border border-secondary border-opacity-25 rounded-3 me-1"
                                        onclick="showInvoiceDetails(
                                            '<?php echo $inv['invoice_number']; ?>',
                                            '<?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?>',
                                            '<?php echo addslashes($inv['customer_name']); ?>',
                                            '<?php echo $inv['timbrado_no']; ?>',
                                            '<?php echo date('d/m/Y', strtotime($inv['timbrado_expiry'])); ?>',
                                            '<?php echo $inv['condition']; ?>',
                                            '<?php echo $inv['sucursal']; ?>',
                                            '<?php echo $inv['total_exento']; ?>',
                                            '<?php echo $inv['total_iva5']; ?>',
                                            '<?php echo $inv['total_iva10']; ?>',
                                            '<?php echo $inv['total_amount']; ?>',
                                            '<?php echo addslashes($inv['notes'] ?? ''); ?>',
                                            '<?php echo htmlspecialchars($itemsJson, ENT_QUOTES, 'UTF-8'); ?>'
                                        )">
                                        <i class="fa fa-eye"></i> Ver
                                    </button>
                                    
                                    <a href="print_invoice.php?id=<?php echo $inv['id']; ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-3 me-1">
                                        <i class="fa fa-print"></i> Imprimir
                                    </a>
                                    
                                    <?php if ($inv['status'] === 'emitida'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3"
                                            onclick="openCancelInvoiceModal('<?php echo $inv['id']; ?>', '<?php echo $inv['invoice_number']; ?>')">
                                            Anular
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-secondary rounded-3" disabled>
                                            Anulada
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Factura de Venta (Manual) -->
<div class="modal fade" id="createManualInvoiceModal" tabindex="-1" aria-labelledby="createManualInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=invoices" method="POST" onsubmit="prepareManualInvoiceSubmit()">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="items_json" id="manual_invoice_items_json">
                <input type="hidden" name="order_id" id="manual_invoice_order_id" value="">
                
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="createManualInvoiceModalLabel">Nueva Factura de Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Fila 1: Modo Facturación, Fecha, Condición -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md">
                            <label class="form-label text-white small font-weight-bold">Modo de Facturación</label>
                            <select name="billing_mode" id="manual_billing_mode" class="form-select form-control-crm" onchange="toggleBillingMode(this.value)">
                                <option value="manual" selected>Forma Manual</option>
                                <option value="pedido">Desde Pedido de Venta</option>
                            </select>
                        </div>
                        <div class="col-12 col-md d-none" id="order_select_container">
                            <label class="form-label text-white small font-weight-bold">Seleccionar Pedido</label>
                            <select id="invoice_order_select" class="form-select form-control-crm" onchange="loadOrderIntoInvoice(this.value)">
                                <option value="">Seleccione Pedido</option>
                                <?php if (!empty($ordersData)): ?>
                                    <?php foreach ($ordersData as $o): ?>
                                        <option value="<?php echo $o['id']; ?>">
                                            Pedido #<?php echo $o['order_code']; ?> - <?php echo htmlspecialchars($o['customer_name'] ?? 'Cliente General'); ?> (<?php echo number_format($o['total_amount'], 0, ',', '.'); ?> Gs.)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md">
                            <label class="form-label text-white small font-weight-bold">Fecha</label>
                            <input type="date" name="invoice_date" class="form-control form-control-crm" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-12 col-md">
                            <label class="form-label text-white small font-weight-bold">Condición</label>
                            <input type="text" class="form-control form-control-crm" value="CONTADO" readonly>
                        </div>
                    </div>

                    <!-- Fila 2: Nro Timbrado, Vencimiento Timbrado -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-white small font-weight-bold">Nro. Timbrado</label>
                            <input type="text" name="timbrado_no" class="form-control form-control-crm" placeholder="12345678" value="12345678">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-white small font-weight-bold">Vencimiento Timbrado</label>
                            <input type="date" name="timbrado_expiry" class="form-control form-control-crm" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Fila 3: Cliente, Sucursal -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label text-white small font-weight-bold">Cliente</label>
                            <select name="customer_id" id="manual_invoice_customer_select" class="form-select form-control-crm" required>
                                <option value="">Seleccione</option>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $cust): ?>
                                        <option value="<?php echo $cust['id']; ?>">
                                            <?php echo htmlspecialchars($cust['name'] . ' (' . ($cust['ruc_ci'] ?: $cust['phone']) . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-white small font-weight-bold">Sucursal</label>
                            <select name="sucursal" class="form-select form-control-crm">
                                <option value="Casa Central" selected>Casa Central</option>
                                <option value="Sucursal 1">Sucursal 1</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fila 4: Observación -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label text-white small font-weight-bold">Observación</label>
                            <input type="text" name="notes" class="form-control form-control-crm" placeholder="Observaciones de la factura">
                        </div>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-4">

                    <!-- Sección Agregar Producto -->
                    <h6 class="text-white-50 font-weight-bold mb-3">Agregar Ítems a Factura</h6>
                    <div class="row g-3 mb-4 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-white small font-weight-bold">Producto</label>
                            <select id="manual_add_product" class="form-select form-control-crm" onchange="updateManualAddProductPrice()">
                                <option value="">Seleccione</option>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $prod): ?>
                                        <option value="<?php echo $prod['id']; ?>" data-name="<?php echo htmlspecialchars($prod['name']); ?>" data-price="<?php echo $prod['base_price']; ?>">
                                            <?php echo htmlspecialchars($prod['name'] . ' - ' . number_format($prod['base_price'], 0, ',', '.') . ' Gs.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label text-white small font-weight-bold">Cantidad</label>
                            <input type="number" id="manual_add_qty" class="form-control form-control-crm text-center" min="1" value="1">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label text-white small font-weight-bold">Precio Unit.</label>
                            <input type="number" id="manual_add_price" class="form-control form-control-crm text-end" min="0" value="0">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label text-white small font-weight-bold">Impuesto (SET)</label>
                            <select id="manual_add_tax" class="form-select form-control-crm">
                                <option value="10" selected>IVA 10% 🍔</option>
                                <option value="5">IVA 5% 🍺</option>
                                <option value="0">Exento 📄</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="button" class="btn btn-info w-100 font-weight-bold text-white rounded-3 py-2" onclick="addManualInvoiceItemRow()">Añadir</button>
                        </div>
                    </div>

                    <!-- Tabla de Ítems de la Factura -->
                    <div class="table-responsive border border-secondary border-opacity-25 rounded-3 mb-4">
                        <table class="table table-dark table-striped table-hover custom-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>PRODUCTO</th>
                                    <th class="text-center" style="width: 100px;">CANT.</th>
                                    <th class="text-end" style="width: 150px;">PRECIO UNIT.</th>
                                    <th class="text-end" style="width: 150px;">EXENTO</th>
                                    <th class="text-end" style="width: 150px;">IVA 5%</th>
                                    <th class="text-end" style="width: 150px;">IVA 10%</th>
                                    <th class="text-center" style="width: 100px;">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody id="manual_invoice_items_body">
                                <tr><td colspan="7" class="text-center text-muted py-4">No hay productos en esta factura.</td></tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-dark bg-opacity-70">
                                    <td colspan="3" class="text-end font-weight-bold">Totales de IVA / Factura:</td>
                                    <td id="manual_total_exento" class="text-end text-white font-weight-bold">0 Gs.</td>
                                    <td id="manual_total_iva5" class="text-end text-white font-weight-bold">0 Gs.</td>
                                    <td id="manual_total_iva10" class="text-end text-white font-weight-bold">0 Gs.</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <h5 class="text-white">Importe Total Factura: <span class="text-warning font-weight-bold display-6 ms-2" id="manual_invoice_total_display">0 Gs.</span></h5>
                    </div>
                </div>
                
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 font-weight-semibold">Confirmar Factura y Emitir ✅</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles de Factura -->
<div class="modal fade" id="viewInvoiceDetailsModal" tabindex="-1" aria-labelledby="viewInvoiceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title font-weight-bold">Detalle de Factura - <span id="detail_inv_number" class="text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4 border-bottom border-secondary border-opacity-25 pb-3">
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="text-muted small">Fecha de Emisión:</div>
                        <div class="text-white font-weight-bold" id="detail_inv_date"></div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="text-muted small">Cliente:</div>
                        <div class="text-white font-weight-bold" id="detail_inv_customer"></div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="text-muted small">Condición:</div>
                        <div class="text-white font-weight-bold" id="detail_inv_condition"></div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="text-muted small">Timbrado Nro:</div>
                        <div class="text-white" id="detail_inv_timbrado"></div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="text-muted small">Sucursal:</div>
                        <div class="text-white" id="detail_inv_sucursal"></div>
                    </div>
                </div>

                <h6 class="text-white-50 font-weight-bold mb-3">Ítems Detallados (Tasa de Impuesto Paraguay)</h6>
                <div class="table-responsive border border-secondary border-opacity-25 rounded-3 mb-4">
                    <table class="table table-dark table-striped table-hover custom-table mb-0 text-xs">
                        <thead>
                            <tr>
                                <th>PRODUCTO</th>
                                <th class="text-center" style="width: 80px;">CANT.</th>
                                <th class="text-end" style="width: 120px;">PRECIO UNIT.</th>
                                <th class="text-end" style="width: 120px;">EXENTO</th>
                                <th class="text-end" style="width: 120px;">IVA 5%</th>
                                <th class="text-end" style="width: 120px;">IVA 10%</th>
                                <th class="text-end" style="width: 120px;">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="detail_inv_items_body">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end font-weight-bold">Desglose de Totales:</td>
                                <td id="detail_total_exento" class="text-end font-weight-bold text-white"></td>
                                <td id="detail_total_iva5" class="text-end font-weight-bold text-white"></td>
                                <td id="detail_total_iva10" class="text-end font-weight-bold text-white"></td>
                                <td id="detail_total_amount" class="text-end font-weight-bold text-warning fs-6"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mb-2">
                    <div class="text-muted small">Observación / Notas:</div>
                    <div class="text-white-50 small" id="detail_inv_notes"></div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25">
                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Anular Factura -->
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-labelledby="cancelInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=invoices" method="POST">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="id" id="cancel_invoice_id">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-danger"><i class="fa fa-exclamation-triangle me-2"></i> Anular Factura</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="small text-muted mb-0">¿Estás seguro de que deseas anular la factura?</p>
                    <h6 class="text-white my-3" id="cancel_invoice_number"></h6>
                    <p class="text-xs text-danger"><i class="fa fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer y quedará registrada en el sistema de auditoría fiscal.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 rounded-3">Anular Factura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const ordersData = <?php echo json_encode($ordersData ?? []); ?>;
let manualInvoiceItems = [];

function toggleBillingMode(mode) {
    const container = document.getElementById('order_select_container');
    if (mode === 'pedido') {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
        document.getElementById('invoice_order_select').value = '';
        document.getElementById('manual_invoice_order_id').value = '';
        manualInvoiceItems = [];
        renderManualInvoiceTable();
        document.getElementById('manual_invoice_customer_select').value = '';
    }
}

function loadOrderIntoInvoice(orderId) {
    document.getElementById('manual_invoice_order_id').value = orderId;
    if (!orderId) {
        manualInvoiceItems = [];
        renderManualInvoiceTable();
        document.getElementById('manual_invoice_customer_select').value = '';
        return;
    }

    const order = ordersData.find(o => parseInt(o.id) === parseInt(orderId));
    if (order) {
        // Autocompletar cliente
        const customerSelect = document.getElementById('manual_invoice_customer_select');
        customerSelect.value = '';
        if (order.customer_id) {
            customerSelect.value = order.customer_id;
        } else if (order.customer_name) {
            for (let option of customerSelect.options) {
                if (option.text.toLowerCase().includes(order.customer_name.toLowerCase())) {
                    customerSelect.value = option.value;
                    break;
                }
            }
        }

        // Cargar ítems
        manualInvoiceItems = [];
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                manualInvoiceItems.push({
                    product_id: parseInt(item.product_id),
                    product_name: item.product_name,
                    quantity: parseInt(item.quantity),
                    unit_price: parseFloat(item.unit_price),
                    tax_rate: 10
                });
            });
        }
        renderManualInvoiceTable();
    }
}

function updateManualAddProductPrice() {
    const select = document.getElementById('manual_add_product');
    const selectedOption = select.options[select.selectedIndex];
    const priceInput = document.getElementById('manual_add_price');
    if (selectedOption && select.value) {
        priceInput.value = selectedOption.getAttribute('data-price');
    } else {
        priceInput.value = 0;
    }
}

function addManualInvoiceItemRow() {
    const select = document.getElementById('manual_add_product');
    const selectedOption = select.options[select.selectedIndex];
    const qtyInput = document.getElementById('manual_add_qty');
    const priceInput = document.getElementById('manual_add_price');
    const taxSelect = document.getElementById('manual_add_tax');

    if (!select.value) {
        alert('Por favor seleccione un producto.');
        return;
    }

    const productId = parseInt(select.value);
    const productName = selectedOption.getAttribute('data-name');
    const quantity = parseInt(qtyInput.value);
    const unitPrice = parseFloat(priceInput.value);
    const taxRate = parseInt(taxSelect.value);

    if (quantity <= 0 || unitPrice < 0) {
        alert('Valores de cantidad o precio inválidos.');
        return;
    }

    // Comprobar si el producto con la misma tasa ya existe en la lista y sumar cantidad
    const existing = manualInvoiceItems.find(item => item.product_id === productId && item.tax_rate === taxRate);
    if (existing) {
        existing.quantity += quantity;
    } else {
        manualInvoiceItems.push({
            product_id: productId,
            product_name: productName,
            quantity: quantity,
            unit_price: unitPrice,
            tax_rate: taxRate
        });
    }

    // Resetear inputs de agregar
    select.value = '';
    qtyInput.value = 1;
    priceInput.value = 0;

    renderManualInvoiceTable();
}

function removeManualInvoiceItem(index) {
    manualInvoiceItems.splice(index, 1);
    renderManualInvoiceTable();
}

function renderManualInvoiceTable() {
    const tbody = document.getElementById('manual_invoice_items_body');
    tbody.innerHTML = '';

    let totalExento = 0;
    let totalIva5 = 0;
    let totalIva10 = 0;
    let totalFactura = 0;

    manualInvoiceItems.forEach((item, index) => {
        const subtotal = item.unit_price * item.quantity;
        totalFactura += subtotal;

        let exento = 0;
        let iva5 = 0;
        let iva10 = 0;

        if (item.tax_rate === 10) {
            iva10 = Math.round(subtotal / 11);
            totalIva10 += iva10;
        } else if (item.tax_rate === 5) {
            iva5 = Math.round(subtotal / 21);
            totalIva5 += iva5;
        } else {
            exento = subtotal;
            totalExento += exento;
        }

        tbody.innerHTML += `
            <tr>
                <td><div class="font-weight-semibold text-white">${escapeHtml(item.product_name)}</div></td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">${item.unit_price.toLocaleString()} Gs.</td>
                <td class="text-end">${exento > 0 ? exento.toLocaleString() + ' Gs.' : '0 Gs.'}</td>
                <td class="text-end">${iva5 > 0 ? iva5.toLocaleString() + ' Gs.' : '0 Gs.'}</td>
                <td class="text-end">${iva10 > 0 ? iva10.toLocaleString() + ' Gs.' : '0 Gs.'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeManualInvoiceItem(${index})">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    if (manualInvoiceItems.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">No hay productos en esta factura.</td></tr>`;
    }

    document.getElementById('manual_total_exento').innerText = totalExento.toLocaleString() + ' Gs.';
    document.getElementById('manual_total_iva5').innerText = totalIva5.toLocaleString() + ' Gs.';
    document.getElementById('manual_total_iva10').innerText = totalIva10.toLocaleString() + ' Gs.';
    document.getElementById('manual_invoice_total_display').innerText = totalFactura.toLocaleString() + ' Gs.';
}

function prepareManualInvoiceSubmit() {
    document.getElementById('manual_invoice_items_json').value = JSON.stringify(manualInvoiceItems);
}

function showInvoiceDetails(invoiceNumber, invoiceDate, customerName, timbradoNo, timbradoExpiry, condition, sucursal, totalExento, totalIva5, totalIva10, totalAmount, notes, itemsJson) {
    document.getElementById('detail_inv_number').innerText = invoiceNumber;
    document.getElementById('detail_inv_date').innerText = invoiceDate;
    document.getElementById('detail_inv_customer').innerText = customerName;
    document.getElementById('detail_inv_condition').innerText = condition;
    document.getElementById('detail_inv_timbrado').innerText = timbradoNo + ' (Vence: ' + timbradoExpiry + ')';
    document.getElementById('detail_inv_sucursal').innerText = sucursal;
    document.getElementById('detail_inv_notes').innerText = notes || 'Ninguna';

    // Rellenar tabla de items
    const tbody = document.getElementById('detail_inv_items_body');
    tbody.innerHTML = '';

    try {
        const items = JSON.parse(itemsJson);
        items.forEach(item => {
            const subtotal = parseFloat(item.subtotal);
            let exento = 0;
            let iva5 = 0;
            let iva10 = 0;

            if (parseInt(item.tax_rate) === 10) {
                iva10 = parseFloat(item.tax_amount);
            } else if (parseInt(item.tax_rate) === 5) {
                iva5 = parseFloat(item.tax_amount);
            } else {
                exento = subtotal;
            }

            tbody.innerHTML += `
                <tr>
                    <td>${escapeHtml(item.product_name)}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">${parseFloat(item.unit_price).toLocaleString()} Gs.</td>
                    <td class="text-end">${exento > 0 ? exento.toLocaleString() + ' Gs.' : '0 Gs.'}</td>
                    <td class="text-end">${iva5 > 0 ? iva5.toLocaleString() + ' Gs.' : '0 Gs.'}</td>
                    <td class="text-end">${iva10 > 0 ? iva10.toLocaleString() + ' Gs.' : '0 Gs.'}</td>
                    <td class="text-end">${subtotal.toLocaleString()} Gs.</td>
                </tr>
            `;
        });
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Error al cargar productos</td></tr>`;
    }

    document.getElementById('detail_total_exento').innerText = parseFloat(totalExento).toLocaleString() + ' Gs.';
    document.getElementById('detail_total_iva5').innerText = parseFloat(totalIva5).toLocaleString() + ' Gs.';
    document.getElementById('detail_total_iva10').innerText = parseFloat(totalIva10).toLocaleString() + ' Gs.';
    document.getElementById('detail_total_amount').innerText = parseFloat(totalAmount).toLocaleString() + ' Gs.';

    const modal = new bootstrap.Modal(document.getElementById('viewInvoiceDetailsModal'));
    modal.show();
}

function openCancelInvoiceModal(id, invoiceNumber) {
    document.getElementById('cancel_invoice_id').value = id;
    document.getElementById('cancel_invoice_number').innerText = invoiceNumber;
    
    const modal = new bootstrap.Modal(document.getElementById('cancelInvoiceModal'));
    modal.show();
}

function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
