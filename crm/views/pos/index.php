<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Módulo de Caja (POS)</h2>
        <?php if ($openRegister): ?>
            <span class="badge bg-success px-3 py-2 rounded-3 text-uppercase"><i class="fa fa-door-open me-1"></i> Caja Abierta</span>
        <?php else: ?>
            <span class="badge bg-danger px-3 py-2 rounded-3 text-uppercase"><i class="fa fa-door-closed me-1"></i> Caja Cerrada</span>
        <?php endif; ?>
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

    <div class="row g-4">
        <!-- Control de Caja (Apertura / Cierre) -->
        <div class="col-12 col-lg-4">
            <?php if (!$openRegister): ?>
                <!-- Formulario de Apertura -->
                <div class="glass-panel p-4 rounded-4 shadow-lg text-center h-100">
                    <i class="fa fa-cash-register fa-4x text-warning mb-3"></i>
                    <h5 class="text-white font-weight-bold mb-3">Abrir Turno de Caja</h5>
                    <p class="text-white-50 small mb-4">Ingrese el saldo inicial de efectivo en caja para comenzar a procesar cobros de pedidos.</p>
                    
                    <form action="index.php?route=pos" method="POST">
                        <input type="hidden" name="action" value="open_register">
                        <div class="mb-4 text-start">
                            <label for="opening_amount" class="form-label text-white small">Saldo Inicial en Efectivo (Gs.) *</label>
                            <input type="number" name="opening_amount" id="opening_amount" min="0" value="200000" step="5000" class="form-control form-control-crm text-center font-weight-bold fs-5" required>
                        </div>
                        <button type="submit" class="btn btn-crm-primary w-100 py-2.5 rounded-3">
                            <i class="fa fa-key me-1"></i> Abrir Caja
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Caja Abierta - Estadísticas de Turno -->
                <div class="glass-panel p-4 rounded-4 shadow-lg h-100">
                    <h5 class="text-white font-weight-bold mb-4 border-bottom border-secondary border-opacity-25 pb-2">Información del Turno</h5>
                    
                    <div class="mb-3 d-flex justify-content-between">
                        <span class="text-white-50 small">Apertura:</span>
                        <span class="text-white font-weight-semibold small"><?php echo date('d/m/Y H:i', strtotime($openRegister['opened_at'])); ?> hs</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <span class="text-white-50 small">Monto Inicial:</span>
                        <span class="text-white font-weight-semibold small"><?php echo number_format($openRegister['opening_amount'], 0, ',', '.'); ?> Gs.</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <span class="text-white-50 small">Ventas de Turno:</span>
                        <span class="text-success font-weight-bold small">+<?php echo number_format($shiftSales, 0, ',', '.'); ?> Gs.</span>
                    </div>
                    <hr class="border-secondary border-opacity-25">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="text-white font-weight-semibold">Total Esperado:</span>
                        <h4 class="text-warning mb-0 font-weight-bold"><?php echo number_format($calculatedAmount, 0, ',', '.'); ?> Gs.</h4>
                    </div>

                    <button type="button" class="btn btn-danger w-100 py-2.5 rounded-3" 
                        onclick="openCloseRegisterModal(
                            '<?php echo $openRegister['id']; ?>',
                            '<?php echo $calculatedAmount; ?>'
                        )">
                        <i class="fa fa-lock me-1"></i> Cerrar Caja / Turno
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Facturas Pendientes de Cobro -->
        <div class="col-12 col-lg-8">
            <div class="glass-panel p-4 rounded-4 shadow-lg">
                <h5 class="text-white font-weight-bold mb-4">Facturas Pendientes de Cobro</h5>

                <div class="table-responsive">
                    <table class="table custom-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Nro. Factura / Fecha</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th class="text-center" style="width: 140px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($unpaidInvoices)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No hay facturas pendientes de cobro.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($unpaidInvoices as $invoice): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                            <div class="text-xs text-white-50 mt-1"><?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?> hs</div>
                                        </td>
                                        <td>
                                            <div class="font-weight-semibold"><?php echo htmlspecialchars($invoice['customer_name']); ?></div>
                                            <?php if ($invoice['customer_ruc']): ?>
                                                <small class="text-white-50">RUC: <?php echo htmlspecialchars($invoice['customer_ruc']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase"><?php echo htmlspecialchars($invoice['billing_mode']); ?></span>
                                        </td>
                                        <td><strong class="text-warning"><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> Gs.</strong></td>
                                        <td class="text-center">
                                            <?php if ($openRegister): ?>
                                                <button type="button" class="btn btn-sm btn-success rounded-3 px-3 py-1.5 font-weight-semibold"
                                                    onclick="openInvoicePaymentModal(
                                                        '<?php echo $invoice['id']; ?>',
                                                        '<?php echo $invoice['invoice_number']; ?>',
                                                        '<?php echo addslashes($invoice['customer_name']); ?>',
                                                        '<?php echo $invoice['total_amount']; ?>'
                                                    )">
                                                    <i class="fa fa-cash-register me-1"></i> Cobrar
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3 py-1.5 font-weight-semibold" disabled title="Abra la caja primero">
                                                    Cobrar
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
    </div>
</div>

<!-- Modal Procesar Cobro (Nueva Factura de Venta) -->
<div class="modal fade" id="processPaymentModal" tabindex="-1" aria-labelledby="processPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=pos" method="POST">
                <input type="hidden" name="action" value="pay_order">
                <input type="hidden" name="order_id" id="payment_order_id">
                <input type="hidden" name="register_id" value="<?php echo $openRegister['id'] ?? ''; ?>">
                
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="processPaymentModalLabel">Procesar Cobro - Pedido #<span id="payment_order_code"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="text-white small font-weight-bold d-block mb-1">Cliente:</span>
                        <div class="fs-6 font-weight-semibold text-warning" id="payment_customer_name"></div>
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-white small font-weight-bold d-block mb-1">Total a Cobrar:</span>
                        <h2 class="text-success font-weight-bold mb-0" id="payment_total_display">0 Gs.</h2>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small font-weight-bold">Método de Pago</label>
                        <select name="payment_method" id="payment_method_select" class="form-select form-control-crm" required>
                            <option value="Efectivo">Efectivo 💵</option>
                            <option value="Tarjeta">Tarjeta POS 💳</option>
                            <option value="Transferencia">Transferencia / Billetera 📱</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small font-weight-bold">Tipo de Comprobante / Impresión</label>
                        <select name="invoice_type" class="form-select form-control-crm" required>
                            <option value="ticket" selected>Ticket Factura (Cinta 80mm) 🧾</option>
                            <option value="recibo">Recibo de dinero (A4/Carta) 📄</option>
                        </select>
                    </div>

                    <hr class="border-secondary border-opacity-25 my-3">

                    <h6 class="text-white-50 font-weight-bold mb-2">Detalle de Productos Facturados:</h6>
                    <div class="table-responsive border border-secondary border-opacity-25 rounded-3">
                        <table class="table table-dark table-striped table-hover custom-table mb-0 text-xs">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center" style="width: 80px;">Cant.</th>
                                    <th class="text-end" style="width: 120px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="payment_items_body">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-3 px-4 font-weight-semibold">Confirmar y Registrar Pago ✅</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cerrar Turno / Caja -->
<div class="modal fade" id="closeRegisterModal" tabindex="-1" aria-labelledby="closeRegisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=pos" method="POST">
                <input type="hidden" name="action" value="close_register">
                <input type="hidden" name="register_id" id="close_register_id">
                <input type="hidden" name="calculated_amount" id="close_calculated_amount">
                
                <div class="modal-header border-secondary border-opacity-25">
                    <h6 class="modal-title font-weight-bold" id="closeRegisterModalLabel">Cerrar Turno de Caja</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="closing_amount" class="form-label text-white small">Monto Real en Caja (Efectivo) *</label>
                        <input type="number" name="closing_amount" id="closing_amount" min="0" class="form-control form-control-crm text-center font-weight-bold" required>
                        <div class="form-text text-white-50 mt-1 small">Ingrese la cantidad física contada. El sistema calculará automáticamente cualquier arqueo o diferencia.</div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25 justify-content-center">
                    <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-3 px-3">Confirmar Cierre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reimprimir Comprobante -->
<div class="modal fade" id="reprintModal" tabindex="-1" aria-labelledby="reprintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <div class="modal-header border-secondary border-opacity-25">
                <h6 class="modal-title font-weight-bold" id="reprintModalLabel">Reimprimir: <span id="reprint_order_code" class="text-warning"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="reprint_order_id">
                <div class="mb-3">
                    <label for="reprint_type_select" class="form-label text-white small">Seleccionar Formato</label>
                    <select id="reprint_type_select" class="form-select form-control-crm">
                        <option value="recibo">Recibo de dinero (A4/Carta) 📄</option>
                        <option value="ticket" selected>Ticket Factura (Cinta térmica 80mm) 🧾</option>
                        <option value="autoimpresa">Factura autoimpresa (A4) 🖨️</option>
                        <option value="electronica">Factura Electrónica (Kude A4) ⚡</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirm_reprint_btn" class="btn btn-sm btn-crm-primary rounded-3 px-3">Imprimir 🖨️</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Procesar Cobro Factura -->
<div class="modal fade" id="processInvoicePaymentModal" tabindex="-1" aria-labelledby="processInvoicePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <form action="index.php?route=pos" method="POST">
                <input type="hidden" name="action" value="pay_invoice">
                <input type="hidden" name="invoice_id" id="payment_invoice_id">
                <input type="hidden" name="register_id" value="<?php echo $openRegister['id'] ?? ''; ?>">
                
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title font-weight-bold" id="processInvoicePaymentModalLabel">Cobrar Factura #<span id="payment_invoice_number" class="text-warning"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="text-white small font-weight-bold d-block mb-1">Cliente:</span>
                        <div class="fs-6 font-weight-semibold text-warning" id="payment_invoice_customer_name"></div>
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-white small font-weight-bold d-block mb-1">Total a Cobrar:</span>
                        <h2 class="text-success font-weight-bold mb-0" id="payment_invoice_total_display">0 Gs.</h2>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small font-weight-bold">Método de Pago</label>
                        <select name="payment_method" class="form-select form-control-crm" required>
                            <option value="Efectivo">Efectivo 💵</option>
                            <option value="Tarjeta">Tarjeta POS 💳</option>
                            <option value="Transferencia">Transferencia / Billetera 📱</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white small font-weight-bold">Modalidad de Impresión (Comprobante SET)</label>
                        <select name="invoice_type" id="invoice_payment_type_select" class="form-select form-control-crm" required>
                            <option value="recibo">Recibo de dinero (A4/Carta) 📄</option>
                            <option value="ticket" selected>Ticket Factura (Cinta térmica 80mm) 🧾</option>
                            <option value="autoimpresa">Factura autoimpresa (A4) 🖨️</option>
                            <option value="electronica">Factura Electrónica (Kude A4) ⚡</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="confirm_invoice_payment_btn" class="btn btn-success rounded-3 px-4 font-weight-semibold">Confirmar y Registrar Pago ✅</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openInvoicePaymentModal(invoiceId, invoiceNumber, customerName, totalAmount) {
    document.getElementById('payment_invoice_id').value = invoiceId;
    document.getElementById('payment_invoice_number').innerText = invoiceNumber;
    document.getElementById('payment_invoice_customer_name').innerText = customerName;
    document.getElementById('payment_invoice_total_display').innerText = parseFloat(totalAmount).toLocaleString() + ' Gs.';
    
    const modal = new bootstrap.Modal(document.getElementById('processInvoicePaymentModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const confirmInvoicePaymentBtn = document.getElementById('confirm_invoice_payment_btn');
    if (confirmInvoicePaymentBtn) {
        confirmInvoicePaymentBtn.addEventListener('click', function() {
            const selectType = document.getElementById('invoice_payment_type_select').value;
            const invoiceId = document.getElementById('payment_invoice_id').value;
            if (invoiceId) {
                window.open('print_invoice.php?id=' + invoiceId + '&type=' + selectType, '_blank');
            }
        });
    }
});
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
