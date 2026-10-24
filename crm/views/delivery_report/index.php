<?php include_once __DIR__ . '/../layout/header.php'; ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-white font-weight-bold">Informe de Repartos por Chofer (Este Mes)</h2>
        <div>
            <a href="print_delivery_report.php" target="_blank" class="btn btn-crm-primary rounded-3 text-sm fw-bold">
                <i class="fa fa-print me-1"></i> Generar Informe
            </a>
        </div>
    </div>

    <!-- Buscador -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="glass-panel p-3 rounded-4 shadow-sm h-100 d-flex align-items-center">
                <div class="input-group w-100">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-white-50"><i class="fa fa-search"></i></span>
                    <input type="text" id="driver_report_search" class="form-control form-control-crm" placeholder="Buscar por chofer o vehículo...">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Informe -->
    <div class="glass-panel p-4 rounded-4 shadow-lg">
        <div class="table-responsive">
            <table class="table custom-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Chofer</th>
                        <th>Teléfono de Contacto</th>
                        <th>Vehículo</th>
                        <th class="text-center">Cant. Repartos Entregados</th>
                        <th class="text-end">Monto Total Pedidos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No se han registrado choferes en el sistema.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td>
                                    <strong class="driver-name-text"><?php echo htmlspecialchars($row['driver_name']); ?></strong>
                                </td>
                                <td>
                                    <span class="text-white-50"><?php echo htmlspecialchars($row['driver_phone'] ?: 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary driver-vehicle-text"><?php echo htmlspecialchars($row['driver_vehicle'] ?: 'N/A'); ?></span>
                                </td>
                                <td class="text-center fw-semibold text-info fs-5">
                                    <?php echo (int)$row['total_deliveries']; ?>
                                </td>
                                <td class="text-end text-success fs-5 fw-bold">
                                    <?php echo number_format($row['total_amount'], 0, ',', '.'); ?> Gs.
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
    const searchInput = document.getElementById('driver_report_search');
    
    function filterDrivers() {
        const query = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.custom-table tbody tr');
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;
            
            const name = row.querySelector('.driver-name-text').textContent.toLowerCase();
            const vehicle = row.querySelector('.driver-vehicle-text').textContent.toLowerCase();
            
            if (name.includes(query) || vehicle.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    if (searchInput) searchInput.addEventListener('input', filterDrivers);
});
</script>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>
