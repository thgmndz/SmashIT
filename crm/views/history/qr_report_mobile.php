<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cobros - SmashIT CRM</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- html2pdf Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0b0c10;
            color: #ffffff;
            padding: 15px;
            margin-bottom: 80px;
        }
        .header-mobile {
            padding: 20px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .card-custom {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .order-code {
            color: #e28743;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .order-meta {
            font-size: 0.85rem;
            color: #a0a6b5;
        }
        .order-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2ec4b6;
        }
        .btn-download-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            z-index: 1000;
        }
        .btn-download {
            background: linear-gradient(135deg, #e28743 0%, #d9701a 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(226, 135, 67, 0.35);
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-download:active {
            transform: scale(0.98);
            box-shadow: 0 4px 10px rgba(226, 135, 67, 0.2);
        }
        /* Style adjustments for PDF rendering */
        .pdf-only-header {
            display: none;
            color: #000000;
        }
        .pdf-theme {
            color: #000000 !important;
            background-color: #ffffff !important;
        }
    </style>
</head>
<body>

    <!-- Contenido a exportar en PDF -->
    <div id="report-content">
        <!-- Encabezado para la visualización en pantalla -->
        <div class="header-mobile text-center">
            <h4 class="fw-bold mb-1">SmashIT Burgers</h4>
            <h5 class="text-white-50 fs-6 fw-normal mb-2">Historial de Cobros</h5>
            <div class="badge bg-secondary bg-opacity-20 text-white-50 border border-secondary border-opacity-25 px-3 py-2 rounded-pill small">
                <i class="fa-regular fa-clock me-1"></i> <?php echo date('d/m/Y H:i'); ?> hs
            </div>
        </div>

        <!-- Lista de Cobros -->
        <div class="container-fluid px-1">
            <?php if (empty($completedOrders)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fa fa-info-circle fa-2x mb-3 text-white-30"></i>
                    <p>No se encontraron cobros registrados.</p>
                </div>
            <?php else: ?>
                <?php foreach ($completedOrders as $order): ?>
                    <div class="card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="order-code"><?php echo htmlspecialchars($order['order_code']); ?></span>
                            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-2 py-1 small">
                                <?php echo htmlspecialchars($order['payment_method']); ?>
                            </span>
                        </div>
                        <div class="row g-1 align-items-center">
                            <div class="col-8">
                                <div class="order-meta mb-1">
                                    <i class="fa fa-user me-1 text-white-30"></i> 
                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                </div>
                                <div class="order-meta">
                                    <i class="fa fa-calendar-alt me-1 text-white-30"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($order['updated_at'])); ?> hs
                                </div>
                                <div class="order-meta mt-1">
                                    <?php 
                                    if ($order['delivery_type'] === 'delivery') {
                                        echo '🚴 Delivery';
                                    } elseif ($order['delivery_type'] === 'pickup') {
                                        echo '🛍️ Local';
                                    } else {
                                        echo '🍽️ Consumo Local';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <span class="order-amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> Gs.</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botón flotante para descargar PDF -->
    <div class="btn-download-container">
        <button class="btn-download" onclick="exportToPDF()">
            <i class="fa fa-file-pdf me-2"></i> Descargar en PDF
        </button>
    </div>

    <script>
        function exportToPDF() {
            // Ocultar temporalmente el contenedor de botones antes de exportar
            const btnContainer = document.querySelector('.btn-download-container');
            btnContainer.style.display = 'none';

            // Crear un clon del contenido para formatearlo para PDF de forma limpia
            const element = document.getElementById('report-content').cloneNode(true);
            
            // Opciones de exportación
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     'SmashIT_Historial_Cobros_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#0b0c10' },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Ejecutar la exportación
            html2pdf().set(opt).from(element).save().then(() => {
                // Volver a mostrar el botón
                btnContainer.style.display = 'block';
            }).catch(err => {
                console.error(err);
                btnContainer.style.display = 'block';
            });
        }
    </script>
</body>
</html>
