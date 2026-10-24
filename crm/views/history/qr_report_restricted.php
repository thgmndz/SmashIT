<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido - SmashIT CRM</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0b0c10;
            color: #ffffff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .restricted-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            animation: fadeIn 0.8s ease;
        }
        .restricted-icon {
            font-size: 64px;
            color: #e28743;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="restricted-card">
        <div class="restricted-icon">
            <i class="fa-solid fa-mobile-screen-button"></i>
        </div>
        <h3 class="fw-bold mb-3">Acceso Restringido</h3>
        <p class="text-white-50 mb-4">
            Este reporte contiene información confidencial y está optimizado únicamente para ser visualizado desde un dispositivo móvil.
        </p>
        <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 text-start">
            <small class="d-block text-center text-white-50">
                <i class="fa fa-info-circle me-1"></i> Por favor, escanee el código QR provisto en el panel de control del CRM desde su celular.
            </small>
        </div>
    </div>
</body>
</html>
