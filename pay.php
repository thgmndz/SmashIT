<?php
require_once __DIR__ . '/config/config.php';

// Leer y decodificar datos del pedido
$dataParam = $_GET['data'] ?? '';
$orderData = null;
$error = null;

if (!empty($dataParam)) {
    try {
        $decoded = base64_decode($dataParam);
        if ($decoded !== false) {
            $orderData = json_decode($decoded, true);
            if (json_last_error() !== JSON_ERROR_NONE || !$orderData) {
                $error = "El formato de los datos del pedido no es válido.";
            }
        } else {
            $error = "No se pudieron decodificar los datos del pedido.";
        }
    } catch (Exception $e) {
        $error = "Error al procesar los datos: " . $e->getMessage();
    }
} else {
    $error = "Faltan los datos del pedido en la URL.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago | SmashIT Burgers</title>
    <meta name="description" content="Procesa tu pago de forma segura desde tu dispositivo móvil.">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap">
    <style>
        :root {
            --bg-dark: #0a0a0c;
            --bg-card: #131317;
            --bg-surface: #1a1a20;
            --primary: #e50914;
            --primary-hover: #ff1e27;
            --accent: #ff4757;
            --accent-gold: #ffb142;
            --text-main: #ffffff;
            --text-muted: #9e9ea7;
            --border-color: rgba(255, 255, 255, 0.08);
            --radius-md: 14px;
            --radius-lg: 20px;
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.5);
            --font-main: 'Outfit', -apple-system, sans-serif;
            --font-heading: 'Space Grotesk', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: var(--font-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .pay-container {
            width: 100%;
            max-width: 480px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 25px;
            box-shadow: var(--shadow-md);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-section img {
            height: 50px;
        }

        .header-title {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }

        .header-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 25px;
        }

        .summary-box {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 15px;
            margin-bottom: 20px;
        }

        .summary-title {
            font-family: var(--font-heading);
            font-size: 1.05rem;
            font-weight: 700;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
            margin-bottom: 12px;
            color: var(--accent-gold);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .summary-row.total {
            border-top: 1px dashed var(--border-color);
            padding-top: 10px;
            margin-top: 10px;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .item-list {
            max-height: 150px;
            overflow-y: auto;
            margin-bottom: 12px;
            padding-right: 5px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .item-row strong {
            color: var(--text-main);
        }

        .btn-pay {
            width: 100%;
            background: var(--primary);
            color: white;
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            padding: 15px;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-pay:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-pay:disabled {
            background: var(--text-dim);
            box-shadow: none;
            cursor: not-allowed;
        }

        .error-box {
            background: rgba(229, 9, 20, 0.1);
            border: 1px solid var(--primary);
            color: #ff6b6b;
            padding: 15px;
            border-radius: var(--radius-md);
            text-align: center;
            margin-bottom: 20px;
        }

        /* Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success Screen */
        .success-screen {
            display: none;
            text-align: center;
            animation: fadeIn 0.5s ease forwards;
        }

        .success-icon {
            font-size: 4rem;
            color: #2ecc71;
            margin-bottom: 15px;
            animation: scaleUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .order-code-badge {
            background: rgba(255, 177, 66, 0.15);
            border: 1px solid var(--accent-gold);
            color: var(--accent-gold);
            font-size: 1.3rem;
            font-weight: 800;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            display: inline-block;
            margin: 15px 0;
            letter-spacing: 1px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes scaleUp {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="pay-container">
        <!-- Logo -->
        <div class="logo-section">
            <img src="logo.png" alt="SmashIT Burgers" onerror="this.style.display='none'">
        </div>

        <?php if ($error): ?>
            <div class="error-box">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
            <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem;">Por favor, vuelve a intentar escanear el código QR desde la pantalla principal.</p>
        <?php else: ?>

            <!-- Pantalla de Pago -->
            <div id="paymentPanel">
                <h1 class="header-title">Pasarela de Pago</h1>
                <p class="header-desc">Billetera Digital / Simulación de Pago SmashIT</p>

                <div class="summary-box">
                    <div class="summary-title">Resumen del Pedido</div>
                    
                    <div class="summary-row">
                        <span>Cliente:</span>
                        <strong><?php echo htmlspecialchars($orderData['customer_name']); ?></strong>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tipo de Entrega:</span>
                        <strong><?php echo $orderData['delivery_type'] === 'delivery' ? '🛵 Envío a Domicilio' : '🏪 Retiro en Local'; ?></strong>
                    </div>

                    <?php if ($orderData['delivery_type'] === 'delivery'): ?>
                        <div class="summary-row" style="flex-direction: column; margin-bottom: 12px;">
                            <span style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 2px;">Dirección de Entrega:</span>
                            <strong style="font-size: 0.85rem;"><?php echo htmlspecialchars($orderData['delivery_address']); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 10px; margin-top: 10px; margin-bottom: 8px;">
                        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">PRODUCTOS:</span>
                    </div>

                    <div class="item-list">
                        <?php foreach ($orderData['items'] as $item): ?>
                            <div class="item-row">
                                <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                                <strong><?php echo number_format($item['subtotal'], 0, ',', '.'); ?> Gs.</strong>
                            </div>
                            <?php if (!empty($item['options_summary'])): ?>
                                <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: -4px; margin-bottom: 6px; padding-left: 10px;">
                                    <?php echo htmlspecialchars($item['options_summary']); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-row" style="border-top: 1px dashed var(--border-color); padding-top: 10px;">
                        <span>Subtotal:</span>
                        <span><?php echo number_format($orderData['subtotal'], 0, ',', '.'); ?> Gs.</span>
                    </div>

                    <div class="summary-row">
                        <span>Envío:</span>
                        <span><?php echo number_format($orderData['delivery_fee'], 0, ',', '.'); ?> Gs.</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total a Pagar:</span>
                        <span style="color: var(--accent-gold);"><?php echo number_format($orderData['total_amount'], 0, ',', '.'); ?> Gs.</span>
                    </div>
                </div>

                <button class="btn-pay" id="confirmPayBtn">
                    <span class="spinner" id="paySpinner"></span>
                    <span id="payBtnText">Confirmar y Pagar 💳</span>
                </button>
            </div>

            <!-- Pantalla de Éxito -->
            <div id="successPanel" class="success-screen">
                <div class="success-icon">✓</div>
                <h1 class="header-title" style="color: #2ecc71;">¡Pago Exitoso!</h1>
                <p class="header-desc">Tu pedido ha sido enviado a la plancha.</p>

                <div class="summary-box" style="margin-top: 20px;">
                    <p style="font-size: 0.9rem;">Código de seguimiento:</p>
                    <div class="order-code-badge" id="orderCodeContainer">SMASH-XXXXXX</div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                        Guarda este código para rastrear el estado de tu pedido desde la pantalla principal de SmashIT.
                    </p>
                </div>

                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 20px;">
                    Ya puedes ver el estado del pedido reflejado en tu pantalla de origen.
                </p>
            </div>

        <?php endif; ?>
    </div>

    <script>
        const orderPayload = <?php echo json_encode($orderData); ?>;

        if (orderPayload) {
            const confirmPayBtn = document.getElementById('confirmPayBtn');
            const paySpinner = document.getElementById('paySpinner');
            const payBtnText = document.getElementById('payBtnText');
            const paymentPanel = document.getElementById('paymentPanel');
            const successPanel = document.getElementById('successPanel');
            const orderCodeContainer = document.getElementById('orderCodeContainer');

            confirmPayBtn.addEventListener('click', async () => {
                confirmPayBtn.disabled = true;
                paySpinner.style.display = 'inline-block';
                payBtnText.textContent = 'Procesando...';

                try {
                    const res = await fetch('api/index.php?action=checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(orderPayload)
                    });

                    const data = await res.json();

                    if (data.status === 'success') {
                        // Ocultar panel de pago, mostrar éxito
                        paymentPanel.style.display = 'none';
                        orderCodeContainer.textContent = data.order_code;
                        successPanel.style.display = 'block';
                    } else {
                        alert(data.message || 'Hubo un error al procesar tu pedido.');
                        confirmPayBtn.disabled = false;
                        paySpinner.style.display = 'none';
                        payBtnText.textContent = 'Confirmar y Pagar 💳';
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error de conexión con el servidor.');
                    confirmPayBtn.disabled = false;
                    paySpinner.style.display = 'none';
                    payBtnText.textContent = 'Confirmar y Pagar 💳';
                }
            });
        }
    </script>
</body>
</html>
