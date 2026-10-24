<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Historial Completo de Cobros</h2>
        <button type="button" class="btn btn-crm-primary rounded-3 px-3 d-flex align-items-center" onclick="openQRReportModal()">
            <i class="fa fa-qrcode me-2"></i> Reportes QR
        </button>
    </div>

    <!-- Tabla de Historial -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha / Hora Cobro</th>
                        <th>Cliente</th>
                        <th>Tipo Entrega</th>
                        <th>Método Pago</th>
                        <th>Monto Cobrado</th>
                        <th class="text-center" style="width: 160px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($completedOrders)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se han registrado cobros históricos en el CRM.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($completedOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($order['updated_at'])); ?></div>
                                    <small class="text-white-50"><?php echo date('H:i', strtotime($order['updated_at'])); ?> hs</small>
                                </td>
                                <td>
                                    <div class="font-weight-semibold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <small class="text-white-50"><?php echo htmlspecialchars($order['notes'] ?: ''); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    if ($order['delivery_type'] === 'delivery') {
                                        echo '🚴 Delivery';
                                    } elseif ($order['delivery_type'] === 'pickup') {
                                        echo '🛍️ Local';
                                    } else {
                                        echo '🍽️ Consumo Local';
                                    }
                                    ?>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($order['payment_method']); ?></span></td>
                                <td><strong class="text-success"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> Gs.</strong></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-dark border border-secondary border-opacity-25 rounded-3 px-3"
                                        onclick="openReprintModal('<?php echo $order['id']; ?>', '<?php echo $order['order_code']; ?>')">
                                        <i class="fa fa-print me-1"></i> Reimprimir
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

<!-- Modal Reporte QR -->
<div class="modal fade" id="qrReportModal" tabindex="-1" aria-labelledby="qrReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title font-weight-bold" id="qrReportModalLabel"><i class="fa fa-qrcode me-2 text-warning"></i>Reporte Móvil QR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-white-50 small mb-4">Escanee este código QR con la cámara de su celular para abrir la tabla de cobros en modo lectura.</p>
                <div id="qrcode-container" class="d-inline-block p-3 bg-white rounded-3 mb-3"></div>
                <div class="mt-2">
                    <small class="text-warning d-block font-monospace" style="word-break: break-all;" id="qr-target-url"></small>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Generator Script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $localIp = gethostbyname(gethostname());
    if ($localIp && $localIp !== '127.0.0.1') {
        $host = str_replace(['localhost', '127.0.0.1'], $localIp, $host);
    }
}
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/SmashIT/crm/index.php';
$mobileURL = $protocol . "://" . $host . $scriptName . "?route=history_qr_mobile";
?>
<script>
    let qrCodeInstance = null;

    function openQRReportModal() {
        const modal = new bootstrap.Modal(document.getElementById('qrReportModal'));
        const container = document.getElementById('qrcode-container');
        const urlLabel = document.getElementById('qr-target-url');
        
        container.innerHTML = ""; // Limpiar qr previo
        
        const mobileURL = '<?php echo $mobileURL; ?>';
        urlLabel.textContent = mobileURL;

        // Generar QR
        qrCodeInstance = new QRCode(container, {
            text: mobileURL,
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        modal.show();
    }
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
