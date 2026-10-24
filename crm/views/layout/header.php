<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmashIT CRM | Panel de Control</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="<?php echo ($_COOKIE['crm-theme'] ?? 'dark') === 'light' ? 'light-theme' : ''; ?>">

    <!-- Sidebar Menu -->
    <?php include_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div id="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-crm glass-panel px-4 rounded-4">
            <div class="container-fluid p-0">
                <button type="button" id="sidebarCollapse" class="toggle-sidebar-btn">
                    <i class="fa fa-bars"></i>
                </button>
                
                <div class="d-flex align-items-center">
                    <button id="btn-theme-toggle" class="btn btn-sm btn-outline-secondary d-flex align-items-center me-2" onclick="ThemeManager.toggle()">
                        <i class="fa fa-moon me-1"></i> Modo Oscuro
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center me-3" data-bs-toggle="modal" data-bs-target="#colorCustomizerModal" title="Personalizar Colores">
                        <i class="fa fa-pencil-alt"></i>
                    </button>
                    <span class="me-3 text-muted">Hola, <strong><?php echo htmlspecialchars($_SESSION['crm_user']['name']); ?></strong></span>

                    <a href="index.php?route=logout" class="btn btn-sm btn-outline-danger rounded-3">

                        <i class="fa fa-sign-out-alt"></i> Salir
                    </a>
                </div>
            </div>
        </nav>

<!-- Modal Personalizador de Colores -->
<div class="modal fade" id="colorCustomizerModal" tabindex="-1" aria-labelledby="colorCustomizerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content bg-dark border border-secondary border-opacity-25 rounded-4 text-white">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title font-weight-bold" id="colorCustomizerModalLabel">Paleta Personalizada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="custom-primary-picker" class="form-label text-muted small d-block">Color Principal</label>
                    <input type="color" id="custom-primary-picker" class="form-control form-control-color w-100 border-0 bg-transparent" value="#e28743" onchange="ColorCustomizer.setPrimary(this.value)">
                </div>
                <div class="mb-3">
                    <label for="custom-secondary-picker" class="form-label text-muted small d-block">Color Secundario</label>
                    <input type="color" id="custom-secondary-picker" class="form-control form-control-color w-100 border-0 bg-transparent" value="#111111" onchange="ColorCustomizer.setSecondary(this.value)">
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-3" onclick="ColorCustomizer.reset()">Restablecer</button>
                <button type="button" class="btn btn-sm btn-secondary rounded-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

