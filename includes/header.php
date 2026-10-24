<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmashIT Burgers | Smashed to Perfection</title>
    <meta name="description" content="Hamburguesas smash artesanales de novillo 100%, queso americano derretido, tocino crocante y salsas de la casa. ¡Pide online con entrega rápida!">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Favicon / Logo Icon -->
    <link rel="icon" type="image/png" href="logo.png">
</head>
<body>

    <!-- Main Navigation Header -->
    <header class="main-header">
        <div class="container">
            <div class="nav-wrapper">
                <a href="index.php" class="brand-logo">
                    <img src="logo.png" alt="SmashIT Burgers Logo">
                </a>

                <div class="search-box">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Buscar Smash Burgers, papas, bebidas...">
                </div>

                <div class="nav-actions">
                    <button class="btn-secondary-action" id="btnTrackOrder">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Seguir Pedido
                    </button>

                    <button class="cart-btn" id="cartBtn">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span>Carrito</span>
                        <span class="cart-badge" id="cartBadge">0</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
