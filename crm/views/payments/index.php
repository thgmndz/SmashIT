<?php include_once __DIR__ . '/../layout/header.php'; 

function formatMonthSpanish(string $period): string {
    $parts = explode('-', $period);
    if (count($parts) < 2) return $period;
    $months = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
    ];
    $monthName = $months[$parts[1]] ?? $parts[1];
    return $monthName . ' ' . $parts[0];
}
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Informe de Recaudación por Método de Pago</h2>
        <div>
            <a href="print_payments_report.php" target="_blank" class="btn btn-crm-primary rounded-3 text-sm fw-bold">
                <i class="fa fa-print me-1"></i> Generar Informe
            </a>
        </div>
    </div>

    <!-- Buscador y Filtro por Método de Pago -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-8">
            <div class="glass-panel p-3 rounded-4 shadow-sm h-100 d-flex align-items-center">
                <div class="input-group w-100">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa fa-search"></i></span>
                    <input type="text" id="report_search" class="form-control form-control-crm" placeholder="Buscar por mes (ej. Agosto)...">
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="glass-panel p-3 rounded-4 shadow-sm h-100 d-flex align-items-center">
                <select id="report_method_filter" class="form-select form-control-crm w-100">
                    <option value="">Todos los Métodos de Pago</option>
                    <option value="Efectivo">Efectivo 💵</option>
                    <option value="Tarjeta">Tarjeta POS 💳</option>
                    <option value="Transferencia">Transferencia / Billetera 📱</option>
                    <option value="QR">Pago con QR 📱</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla de Reporte -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Método de Pago</th>
                        <th class="text-center">Cant. Pedidos Procesados</th>
                        <th class="text-end">Total Recaudado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No se han registrado cobros para generar informes.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): 
                            $methodBadge = 'bg-secondary';
                            $method = $row['payment_method'];
                            if (stripos($method, 'Efectivo') !== false) {
                                $methodBadge = 'bg-success';
                            } elseif (stripos($method, 'Tarjeta') !== false) {
                                $methodBadge = 'bg-info';
                            } elseif (stripos($method, 'Transferencia') !== false) {
                                $methodBadge = 'bg-primary';
                            } elseif (stripos($method, 'QR') !== false) {
                                $methodBadge = 'bg-warning text-dark';
                            }
                            $spanishMonth = formatMonthSpanish($row['month_period']);
                        ?>
                            <tr>
                                <td>
                                    <strong class="month-cell-text"><?php echo htmlspecialchars($spanishMonth); ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?php echo $methodBadge; ?> px-3 py-1.5 rounded-3 fw-bold payment-method-badge">
                                        <?php echo htmlspecialchars($row['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="text-center fw-semibold text-info">
                                    <?php echo (int)$row['total_orders']; ?>
                                </td>
                                <td class="text-end text-success fs-5 fw-bold">
                                    <?php echo number_format($row['total_collected'], 0, ',', '.'); ?> Gs.
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('report_search');
    const filterSelect = document.getElementById('report_method_filter');
    
    function filterReport() {
        const query = searchInput.value.toLowerCase().trim();
        const methodFilter = filterSelect.value.toLowerCase();
        
        const rows = document.querySelectorAll('.custom-table tbody tr');
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            
            const monthText = row.querySelector('.month-cell-text').textContent.toLowerCase();
            const method = row.querySelector('.payment-method-badge').textContent.toLowerCase();
            
            const matchesQuery = monthText.includes(query);
            const matchesMethod = methodFilter === '' || method.includes(methodFilter);
            
            if (matchesQuery && matchesMethod) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    if (searchInput) searchInput.addEventListener('input', filterReport);
    if (filterSelect) filterSelect.addEventListener('change', filterReport);
});
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
