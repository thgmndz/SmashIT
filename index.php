<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Database.php';

// Inicializar base de datos y migrar si es necesario
SmashIT\Database::getConnection();

include __DIR__ . '/includes/header.php';
?>
<script>
    const SERVER_LOCAL_IP = <?php echo json_encode(gethostbyname(gethostname())); ?>;
</script>

<!-- Hero Banner -->
<section class="hero-section">
    <div class="hero-glow"></div>
    <div class="container hero-content">
        <span class="hero-tag">🔥 100% CARNE DE NOVILLO SELECCIONADA</span>
        <h1 class="hero-title">SMASHED TO <span>PERFECTION</span></h1>
        <p class="hero-desc">Doble o triple carne smash a la plancha. ¡HACELO COMBO! Sumá Papas Fritas Medianas + Gaseosa 500ml a cualquier burger por solo +15.000 Gs.</p>
    </div>
</section>

<!-- Categorías y Productos -->
<main class="container">
    <nav class="category-nav" id="categoryNav">
        <!-- Renderizado dinámico desde JS -->
    </nav>

    <div class="product-grid" id="productGrid">
        <!-- Renderizado dinámico desde JS -->
    </div>
</main>

<!-- Slide-out Cart Drawer -->
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-header">
        <h2 class="cart-title">Tu Pedido</h2>
        <button class="btn-close-modal" id="closeCartBtn">&times;</button>
    </div>

    <div class="cart-body" id="cartBody">
        <!-- Renderizado dinámico desde JS -->
    </div>

    <div class="cart-footer">
        <div class="summary-row">
            <span>Subtotal</span>
            <span id="cartSubtotal">0 Gs.</span>
        </div>
        <div class="summary-row">
            <span>Envío estimado</span>
            <span id="cartDeliveryFee">10.000 Gs.</span>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <span id="cartTotal">0 Gs.</span>
        </div>

        <button class="btn-checkout" id="btnCheckout">Continuar al Checkout 🚀</button>
    </div>
</div>

<!-- Modal de Personalización de Producto -->
<div class="modal-overlay" id="optionModal">
    <div class="modal-container">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="optionModalTitle">Personalizar</h3>
                <p id="optionModalDesc" style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;"></p>
            </div>
            <button class="btn-close-modal" id="closeOptionModalBtn">&times;</button>
        </div>

        <div class="modal-body" id="optionModalBody">
            <!-- Opciones cargadas dinámicamente -->
        </div>

        <div class="modal-footer">
            <div class="modal-total-price" id="optionModalTotalPrice">0 Gs.</div>
            <button class="btn-confirm-add" id="btnConfirmAdd">Agregar al Carrito 🍔</button>
        </div>
    </div>
</div>

<!-- Modal de Checkout / Datos del Cliente -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Finalizar Pedido</h3>
            <button class="btn-close-modal" id="closeCheckoutModalBtn">&times;</button>
        </div>

        <form id="checkoutForm">
            <div class="modal-body">
                <div class="delivery-type-selector">
                    <button type="button" class="type-btn active" id="typeDeliveryBtn">🛵 Envío a Domicilio</button>
                    <button type="button" class="type-btn" id="typePickupBtn">🏪 Retiro en Local</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre y Apellido *</label>
                    <input type="text" name="customer_name" class="form-input" placeholder="Ej. Juan Pérez" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono de Contacto (WhatsApp) *</label>
                    <input type="tel" name="customer_phone" class="form-input" placeholder="Ej. 0981 123 456" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico *</label>
                    <input type="email" name="customer_email" class="form-input" placeholder="ejemplo@email.com" required>
                </div>

                <div class="form-group" id="addressGroup">
                    <label class="form-label">Dirección de Entrega *</label>
                    <input type="text" name="delivery_address" id="delivery_address" class="form-input" placeholder="Calle, número, departamento o referencias">
                    <div id="checkoutMap" style="height: 220px; width: 100%; border-radius: var(--radius-md); margin-top: 10px; border: 1px solid var(--border-color); position: relative; z-index: 1;"></div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 6px;">📍 Haz clic o arrastra el marcador en el mapa para marcar tu ubicación.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Método de Pago *</label>
                    <select name="payment_method" class="form-input" style="background: var(--bg-surface);">
                        <option value="Efectivo al Entregar">💵 Efectivo al entregar</option>
                        <option value="Transferencia / Billetera">📱 Transferencia / Billetera digital</option>
                        <option value="Tarjeta POS al Entregar">💳 Tarjeta (POS Móvil)</option>
                        <option value="QR">📱 Pago con QR (Escanear con Celular)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas Adicionales (Opcional)</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Ej. Sin cebolla, picaporte verde..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Total a pagar</span>
                    <strong style="font-size: 1.3rem;" id="checkoutTotalAmount">0 Gs.</strong>
                </div>
                <button type="submit" class="btn-confirm-add">Confirmar Pedido 🔥</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Pago QR -->
<div class="modal-overlay" id="qrPaymentModal">
    <div class="modal-container" style="max-width: 400px; text-align: center;">
        <div class="modal-header">
            <h3 class="modal-title">Pago con Código QR</h3>
            <button class="btn-close-modal" id="closeQrPaymentModalBtn">&times;</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; align-items: center; gap: 15px; padding: 20px;">
            <p style="font-size: 0.95rem; color: var(--text-muted);">Escanea el código QR con la cámara de tu celular para completar el pago y generar tu pedido.</p>
            <div id="qrCodeContainer" style="background: white; padding: 15px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: flex; justify-content: center; align-items: center; min-height: 250px; min-width: 250px;">
                <!-- Imagen del código QR cargada dinámicamente -->
            </div>
            <div style="font-size: 1.15rem; font-weight: 700; margin-top: 10px;">
                Total a Pagar: <span id="qrTotalAmount" style="color: var(--accent-gold);">0 Gs.</span>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                Una vez confirmado el pago desde tu móvil, tu pedido se procesará automáticamente.
            </div>
        </div>
    </div>
</div>

<!-- Modal de Rastreo / Estado del Pedido -->
<div class="modal-overlay" id="trackerModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Seguimiento de Pedido</h3>
            <button class="btn-close-modal" id="closeTrackerModalBtn">&times;</button>
        </div>

        <div class="modal-body">
            <form id="trackerForm" style="margin-bottom: 20px;">
                <div class="form-group" style="display: flex; gap: 10px;">
                    <input type="text" id="trackerInput" class="form-input" placeholder="Ingresa tu código (ej. SMASH-A1B2)" required>
                    <button type="submit" class="btn-add-cart" style="padding: 0 20px; white-space: nowrap;">Buscar</button>
                </div>
            </form>

            <div id="trackerResult">
                <!-- Se rellena dinámicamente con el estado -->
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
