<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmashIT CRM | Login</title>
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
<body class="login-bg">

    <div class="login-card glass-panel shadow-lg border border-secondary border-opacity-25 text-center">
        <h2 class="text-white mb-2 font-weight-bold">SmashIT <span style="color: var(--primary-color);">CRM</span></h2>
        <p class="text-muted mb-4 small">Por favor, inicie sesión para acceder al panel</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-danger small text-start rounded-3" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?route=login" method="POST">
            <div class="mb-3 text-start">
                <label for="username" class="form-label text-muted small">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary border-opacity-25 text-muted">
                        <i class="fa fa-user"></i>
                    </span>
                    <input type="text" name="username" id="username" class="form-control form-control-crm border-start-0 ps-0" placeholder="admin" required autofocus>
                </div>
            </div>
            
            <div class="mb-4 text-start">
                <label for="password" class="form-label text-muted small">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary border-opacity-25 text-muted">
                        <i class="fa fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" class="form-control form-control-crm border-start-0 ps-0" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-crm-primary w-100 py-2.5 rounded-3 mb-3">
                <i class="fa fa-sign-in-alt me-2"></i> Ingresar
            </button>
        </form>

        <div class="border-top border-secondary border-opacity-25 pt-3 mt-3">
            <div class="bg-dark bg-opacity-50 p-3 rounded-3 text-start small">
                <div class="text-warning mb-1 font-weight-bold text-center"><i class="fa fa-info-circle me-1"></i> Credenciales de Prueba</div>
                <div class="text-muted d-flex justify-content-between"><span>Usuario:</span> <code class="text-white">admin</code></div>
                <div class="text-muted d-flex justify-content-between"><span>Contraseña:</span> <code class="text-white">123456</code></div>
                <div class="text-muted mt-2 text-center" style="font-size: 10px;">Seguridad: MD5 Hash activo</div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
