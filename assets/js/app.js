/**
 * SmashIT Burgers - Application Logic (ES6 Modular Architecture)
 */

document.addEventListener('DOMContentLoaded', () => {
    SmashITApp.init();
});

const SmashITApp = {
    state: {
        products: [],
        categories: [],
        activeCategory: null,
        searchQuery: '',
        cart: JSON.parse(localStorage.getItem('smashit_cart')) || [],
        selectedProductForModal: null,
        deliveryFee: 10000,
        deliveryType: 'delivery',
        qrPollInterval: null,
        checkoutMap: null,
        checkoutMarker: null
    },

    init() {
        this.cacheDOM();
        this.bindEvents();
        this.loadCatalog();
        this.updateCartUI();
    },

    formatCurrency(val) {
        if (isNaN(val)) return '0 Gs.';
        return new Intl.NumberFormat('es-PY').format(Math.round(val)) + ' Gs.';
    },

    cacheDOM() {
        this.dom = {
            productGrid: document.getElementById('productGrid'),
            categoryNav: document.getElementById('categoryNav'),
            searchInput: document.getElementById('searchInput'),
            cartBtn: document.getElementById('cartBtn'),
            cartBadge: document.getElementById('cartBadge'),
            cartDrawer: document.getElementById('cartDrawer'),
            closeCartBtn: document.getElementById('closeCartBtn'),
            cartBody: document.getElementById('cartBody'),
            cartSubtotal: document.getElementById('cartSubtotal'),
            cartDeliveryFee: document.getElementById('cartDeliveryFee'),
            cartTotal: document.getElementById('cartTotal'),
            btnCheckout: document.getElementById('btnCheckout'),

            // Modals
            optionModal: document.getElementById('optionModal'),
            closeOptionModalBtn: document.getElementById('closeOptionModalBtn'),
            optionModalTitle: document.getElementById('optionModalTitle'),
            optionModalDesc: document.getElementById('optionModalDesc'),
            optionModalBody: document.getElementById('optionModalBody'),
            optionModalTotalPrice: document.getElementById('optionModalTotalPrice'),
            btnConfirmAdd: document.getElementById('btnConfirmAdd'),

            // Checkout Modal
            checkoutModal: document.getElementById('checkoutModal'),
            closeCheckoutModalBtn: document.getElementById('closeCheckoutModalBtn'),
            checkoutForm: document.getElementById('checkoutForm'),
            typeDeliveryBtn: document.getElementById('typeDeliveryBtn'),
            typePickupBtn: document.getElementById('typePickupBtn'),
            addressGroup: document.getElementById('addressGroup'),
            checkoutTotalAmount: document.getElementById('checkoutTotalAmount'),

            // Tracking Modal
            trackerModal: document.getElementById('trackerModal'),
            closeTrackerModalBtn: document.getElementById('closeTrackerModalBtn'),
            btnTrackOrder: document.getElementById('btnTrackOrder'),
            trackerForm: document.getElementById('trackerForm'),
            trackerInput: document.getElementById('trackerInput'),
            trackerResult: document.getElementById('trackerResult'),
            toastContainer: document.getElementById('toastContainer'),

            // QR Payment Modal
            qrPaymentModal: document.getElementById('qrPaymentModal'),
            closeQrPaymentModalBtn: document.getElementById('closeQrPaymentModalBtn'),
            qrCodeContainer: document.getElementById('qrCodeContainer'),
            qrTotalAmount: document.getElementById('qrTotalAmount')
        };
    },

    bindEvents() {
        // Buscador
        if (this.dom.searchInput) {
            this.dom.searchInput.addEventListener('input', (e) => {
                this.state.searchQuery = e.target.value;
                this.renderProducts();
            });
        }

        // Abrir/Cerrar Carrito
        if (this.dom.cartBtn) {
            this.dom.cartBtn.addEventListener('click', () => this.toggleCart(true));
        }
        if (this.dom.closeCartBtn) {
            this.dom.closeCartBtn.addEventListener('click', () => this.toggleCart(false));
        }

        // Modal de Opciones
        if (this.dom.closeOptionModalBtn) {
            this.dom.closeOptionModalBtn.addEventListener('click', () => this.closeOptionModal());
        }
        if (this.dom.btnConfirmAdd) {
            this.dom.btnConfirmAdd.addEventListener('click', () => this.confirmAddProductWithOption());
        }

        // Checkout Modal
        if (this.dom.btnCheckout) {
            this.dom.btnCheckout.addEventListener('click', () => this.openCheckoutModal());
        }
        if (this.dom.closeCheckoutModalBtn) {
            this.dom.closeCheckoutModalBtn.addEventListener('click', () => this.closeCheckoutModal());
        }
        if (this.dom.typeDeliveryBtn && this.dom.typePickupBtn) {
            this.dom.typeDeliveryBtn.addEventListener('click', () => this.setDeliveryType('delivery'));
            this.dom.typePickupBtn.addEventListener('click', () => this.setDeliveryType('pickup'));
        }
        if (this.dom.checkoutForm) {
            this.dom.checkoutForm.addEventListener('submit', (e) => this.handleCheckoutSubmit(e));
        }

        // Tracker Modal
        if (this.dom.btnTrackOrder) {
            this.dom.btnTrackOrder.addEventListener('click', () => this.openTrackerModal());
        }
        if (this.dom.closeTrackerModalBtn) {
            this.dom.closeTrackerModalBtn.addEventListener('click', () => this.closeTrackerModal());
        }
        if (this.dom.trackerForm) {
            this.dom.trackerForm.addEventListener('submit', (e) => this.handleTrackerSubmit(e));
        }

        // QR Payment Modal
        if (this.dom.closeQrPaymentModalBtn) {
            this.dom.closeQrPaymentModalBtn.addEventListener('click', () => this.closeQrPaymentModal());
        }
    },

    async loadCatalog() {
        try {
            const res = await fetch('api/index.php?action=products');
            const data = await res.json();

            if (data.status === 'success') {
                this.state.products = data.products;
                this.state.categories = data.categories;
                this.renderCategories();
                this.renderProducts();
            } else {
                this.showToast('Error cargando productos', 'error');
            }
        } catch (err) {
            console.error('Error al conectar con la API:', err);
            this.showToast('Error de conexión con el servidor', 'error');
        }
    },

    renderCategories() {
        if (!this.dom.categoryNav) return;

        let html = `
            <button class="category-tab ${this.state.activeCategory === null ? 'active' : ''}" data-category="all">
                🔥 Todos
            </button>
        `;

        this.state.categories.forEach(cat => {
            let icon = '🍔';
            if (cat.slug === 'sides') icon = '🍟';
            if (cat.slug === 'drinks') icon = '🥤';
            if (cat.slug === 'desserts') icon = '🍪';

            html += `
                <button class="category-tab ${this.state.activeCategory === cat.id ? 'active' : ''}" data-category="${cat.id}">
                    ${icon} ${cat.name}
                </button>
            `;
        });

        this.dom.categoryNav.innerHTML = html;

        this.dom.categoryNav.querySelectorAll('.category-tab').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const catAttr = btn.getAttribute('data-category');
                this.state.activeCategory = catAttr === 'all' ? null : parseInt(catAttr);
                
                this.dom.categoryNav.querySelectorAll('.category-tab').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                this.renderProducts();
            });
        });
    },

    renderProducts() {
        if (!this.dom.productGrid) return;

        let filtered = this.state.products;

        if (this.state.activeCategory !== null) {
            filtered = filtered.filter(p => p.category_id === this.state.activeCategory);
        }

        if (this.state.searchQuery.trim() !== '') {
            const q = this.state.searchQuery.toLowerCase();
            filtered = filtered.filter(p => p.name.toLowerCase().includes(q) || p.description.toLowerCase().includes(q));
        }

        if (filtered.length === 0) {
            this.dom.productGrid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                    <p style="font-size: 1.2rem; font-weight: 700;">No se encontraron productos.</p>
                </div>
            `;
            return;
        }

        let html = '';
        filtered.forEach(p => {
            const badgeHtml = p.badge ? `<span class="product-badge">${p.badge}</span>` : '';
            const priceFormatted = this.formatCurrency(p.base_price);

            html += `
                <div class="product-card">
                    ${badgeHtml}
                    <div class="product-image-wrap">
                        <img src="${p.image_url || 'assets/images/classic-smash.jpg'}" alt="${p.name}" loading="lazy" onerror="this.src='assets/images/classic-smash.jpg'">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">${p.name}</h3>
                        <p class="product-desc">${p.description}</p>
                        <div class="product-bottom">
                            <span class="product-price">${priceFormatted}</span>
                            <button class="btn-add-cart" onclick="SmashITApp.handleProductClick(${p.id})">
                                + Agregar
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        this.dom.productGrid.innerHTML = html;
    },

    async handleProductClick(productId) {
        try {
            const res = await fetch(`api/index.php?action=product_detail&id=${productId}`);
            const data = await res.json();

            if (data.status === 'success' && data.product) {
                const product = data.product;
                if (product.option_groups && product.option_groups.length > 0) {
                    this.openOptionModal(product);
                } else {
                    // Agregar directamente si no requiere personalización
                    this.addToCart({
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.base_price),
                        quantity: 1,
                        options_summary: '',
                        cartKey: `p_${product.id}`
                    });
                }
            }
        } catch (err) {
            console.error('Error al cargar detalle:', err);
        }
    },

    openOptionModal(product) {
        this.state.selectedProductForModal = product;

        this.dom.optionModalTitle.textContent = product.name;
        this.dom.optionModalDesc.textContent = product.description;

        let bodyHtml = '';

        product.option_groups.forEach(group => {
            const isRadio = group.type === 'radio';
            bodyHtml += `
                <div class="option-group-block" data-group-id="${group.id}">
                    <div class="option-group-title">
                        ${group.name}
                        <span class="tag">${group.is_required ? '(Requerido)' : '(Opcional)'}</span>
                    </div>
                    <div class="option-items-list">
            `;

            group.items.forEach((item, idx) => {
                const checked = (isRadio && (item.is_default || idx === 0)) ? 'checked' : '';
                const priceMod = parseFloat(item.price_modifier);
                const priceText = priceMod > 0 ? `+${this.formatCurrency(priceMod)}` : 'Incluido';

                bodyHtml += `
                    <label class="option-item-label">
                        <div class="option-item-info">
                            <input type="${isRadio ? 'radio' : 'checkbox'}" 
                                   name="group_${group.id}" 
                                   value="${item.id}" 
                                   data-name="${item.name}" 
                                   data-price="${priceMod}" 
                                   ${checked} 
                                   onchange="SmashITApp.recalculateModalTotal()">
                            <span class="option-item-name">${item.name}</span>
                        </div>
                        <span class="option-item-price">${priceText}</span>
                    </label>
                `;
            });

            bodyHtml += `
                    </div>
                </div>
            `;
        });

        this.dom.optionModalBody.innerHTML = bodyHtml;
        this.recalculateModalTotal();

        this.dom.optionModal.classList.add('active');
    },

    closeOptionModal() {
        this.dom.optionModal.classList.remove('active');
        this.state.selectedProductForModal = null;
    },

    recalculateModalTotal() {
        if (!this.state.selectedProductForModal) return;

        let total = parseFloat(this.state.selectedProductForModal.base_price);

        const inputs = this.dom.optionModalBody.querySelectorAll('input:checked');
        inputs.forEach(input => {
            total += parseFloat(input.getAttribute('data-price') || 0);
        });

        this.dom.optionModalTotalPrice.textContent = this.formatCurrency(total);
    },

    confirmAddProductWithOption() {
        const product = this.state.selectedProductForModal;
        if (!product) return;

        let basePrice = parseFloat(product.base_price);
        let optionsPrice = 0;
        let selectedOptionsNames = [];
        let selectedOptionsIds = [];

        const inputs = this.dom.optionModalBody.querySelectorAll('input:checked');
        inputs.forEach(input => {
            const priceMod = parseFloat(input.getAttribute('data-price') || 0);
            optionsPrice += priceMod;
            selectedOptionsNames.push(input.getAttribute('data-name'));
            selectedOptionsIds.push(input.value);
        });

        const finalUnitPrice = basePrice + optionsPrice;
        const optionsSummary = selectedOptionsNames.join(', ');
        const cartKey = `p_${product.id}_opts_${selectedOptionsIds.sort().join('_')}`;

        this.addToCart({
            id: product.id,
            name: product.name,
            price: finalUnitPrice,
            quantity: 1,
            options_summary: optionsSummary,
            cartKey: cartKey
        });

        this.closeOptionModal();
    },

    addToCart(item) {
        const existingIndex = this.state.cart.findIndex(i => i.cartKey === item.cartKey);

        if (existingIndex > -1) {
            this.state.cart[existingIndex].quantity += 1;
        } else {
            this.state.cart.push(item);
        }

        this.saveCart();
        this.updateCartUI();
        this.showToast(`¡${item.name} agregado al carrito!`, 'success');
        this.toggleCart(true);
    },

    updateQuantity(cartKey, delta) {
        const itemIndex = this.state.cart.findIndex(i => i.cartKey === cartKey);
        if (itemIndex > -1) {
            this.state.cart[itemIndex].quantity += delta;
            if (this.state.cart[itemIndex].quantity <= 0) {
                this.state.cart.splice(itemIndex, 1);
            }
            this.saveCart();
            this.updateCartUI();
        }
    },

    saveCart() {
        localStorage.setItem('smashit_cart', JSON.stringify(this.state.cart));
    },

    updateCartUI() {
        const totalItems = this.state.cart.reduce((sum, item) => sum + item.quantity, 0);
        if (this.dom.cartBadge) {
            this.dom.cartBadge.textContent = totalItems;
        }

        if (!this.dom.cartBody) return;

        if (this.state.cart.length === 0) {
            this.dom.cartBody.innerHTML = `
                <div class="cart-empty">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <p style="font-weight: 700; font-size: 1.1rem; color: var(--text-main);">Tu carrito está vacío</p>
                    <p style="font-size: 0.88rem; margin-top: 4px;">Elige tus hamburguesas favoritas y arma tu combo ideal.</p>
                </div>
            `;
            this.dom.cartSubtotal.textContent = this.formatCurrency(0);
            this.dom.cartDeliveryFee.textContent = this.formatCurrency(0);
            this.dom.cartTotal.textContent = this.formatCurrency(0);
            return;
        }

        let subtotal = 0;
        let html = '';

        this.state.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            html += `
                <div class="cart-item">
                    <div class="cart-item-details">
                        <div class="cart-item-title">${item.name}</div>
                        ${item.options_summary ? `<div class="cart-item-options">${item.options_summary}</div>` : ''}
                        <div class="cart-item-bottom">
                            <span class="cart-item-price">${this.formatCurrency(itemTotal)}</span>
                            <div class="qty-controls">
                                <button class="qty-btn" onclick="SmashITApp.updateQuantity('${item.cartKey}', -1)">-</button>
                                <span class="qty-number">${item.quantity}</span>
                                <button class="qty-btn" onclick="SmashITApp.updateQuantity('${item.cartKey}', 1)">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        this.dom.cartBody.innerHTML = html;

        const currentFee = this.state.deliveryType === 'delivery' ? this.state.deliveryFee : 0;
        const grandTotal = subtotal + currentFee;

        this.dom.cartSubtotal.textContent = this.formatCurrency(subtotal);
        this.dom.cartDeliveryFee.textContent = this.formatCurrency(currentFee);
        this.dom.cartTotal.textContent = this.formatCurrency(grandTotal);
    },

    toggleCart(open) {
        if (!this.dom.cartDrawer) return;
        if (open) {
            this.dom.cartDrawer.classList.add('open');
        } else {
            this.dom.cartDrawer.classList.remove('open');
        }
    },

    setDeliveryType(type) {
        this.state.deliveryType = type;
        if (type === 'delivery') {
            this.dom.typeDeliveryBtn.classList.add('active');
            this.dom.typePickupBtn.classList.remove('active');
            this.dom.addressGroup.style.display = 'block';
            setTimeout(() => this.initCheckoutMap(), 100);
        } else {
            this.dom.typePickupBtn.classList.add('active');
            this.dom.typeDeliveryBtn.classList.remove('active');
            this.dom.addressGroup.style.display = 'none';
        }
        this.updateCartUI();
    },

    openCheckoutModal() {
        if (this.state.cart.length === 0) {
            this.showToast('Agrega productos al carrito primero', 'error');
            return;
        }

        const subtotal = this.state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const fee = this.state.deliveryType === 'delivery' ? this.state.deliveryFee : 0;
        const total = subtotal + fee;

        this.dom.checkoutTotalAmount.textContent = this.formatCurrency(total);
        this.toggleCart(false);
        this.dom.checkoutModal.classList.add('active');

        if (this.state.deliveryType === 'delivery') {
            setTimeout(() => this.initCheckoutMap(), 100);
        }
    },

    closeCheckoutModal() {
        this.dom.checkoutModal.classList.remove('active');
    },

    initCheckoutMap() {
        const mapContainer = document.getElementById('checkoutMap');
        if (!mapContainer) return;

        if (this.state.checkoutMap) {
            setTimeout(() => this.state.checkoutMap.invalidateSize(), 200);
            return;
        }

        // Posición inicial por defecto: Asunción, Paraguay
        const defaultLat = -25.2866;
        const defaultLon = -57.6111;

        this.state.checkoutMap = L.map('checkoutMap').setView([defaultLat, defaultLon], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.state.checkoutMap);

        // Crear marcador arrastrable
        this.state.checkoutMarker = L.marker([defaultLat, defaultLon], {
            draggable: true
        }).addTo(this.state.checkoutMap);

        const updateAddressFromLatLng = async (lat, lon) => {
            const addressInput = document.getElementById('delivery_address');
            if (addressInput) {
                addressInput.value = `Cargando dirección... [Ubicación: https://www.google.com/maps?q=${lat},${lon}]`;
            }
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
                const data = await response.json();
                if (data && data.display_name) {
                    const cleanAddress = data.display_name;
                    if (addressInput) {
                        addressInput.value = `${cleanAddress} [Ubicación: https://www.google.com/maps?q=${lat},${lon}]`;
                    }
                } else {
                    if (addressInput) {
                        addressInput.value = `Ubicación marcada [Ubicación: https://www.google.com/maps?q=${lat},${lon}]`;
                    }
                }
            } catch (err) {
                console.error(err);
                if (addressInput) {
                    addressInput.value = `Ubicación marcada [Ubicación: https://www.google.com/maps?q=${lat},${lon}]`;
                }
            }
        };

        // Escuchar eventos de arrastre del marcador
        this.state.checkoutMarker.on('dragend', () => {
            const position = this.state.checkoutMarker.getLatLng();
            updateAddressFromLatLng(position.lat, position.lng);
        });

        // Escuchar clics en el mapa para mover el marcador
        this.state.checkoutMap.on('click', (e) => {
            this.state.checkoutMarker.setLatLng(e.latlng);
            updateAddressFromLatLng(e.latlng.lat, e.latlng.lng);
        });

        // Intentar obtener la ubicación actual del usuario con geolocalización
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                const userLat = pos.coords.latitude;
                const userLon = pos.coords.longitude;
                this.state.checkoutMap.setView([userLat, userLon], 16);
                this.state.checkoutMarker.setLatLng([userLat, userLon]);
                updateAddressFromLatLng(userLat, userLon);
            }, (err) => {
                console.warn('Geolocation failed:', err);
                updateAddressFromLatLng(defaultLat, defaultLon);
            });
        } else {
            updateAddressFromLatLng(defaultLat, defaultLon);
        }
    },

    closeQrPaymentModal() {
        if (this.dom.qrPaymentModal) {
            this.dom.qrPaymentModal.classList.remove('active');
        }
        if (this.state.qrPollInterval) {
            clearInterval(this.state.qrPollInterval);
            this.state.qrPollInterval = null;
        }
    },

    async handleCheckoutSubmit(e) {
        e.preventDefault();

        const formData = new FormData(this.dom.checkoutForm);
        const subtotal = this.state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const fee = this.state.deliveryType === 'delivery' ? this.state.deliveryFee : 0;

        const payload = {
            customer_name: formData.get('customer_name'),
            customer_email: formData.get('customer_email'),
            customer_phone: formData.get('customer_phone'),
            delivery_type: this.state.deliveryType,
            delivery_address: formData.get('delivery_address') || 'Retiro en Local',
            payment_method: formData.get('payment_method'),
            notes: formData.get('notes') || '',
            subtotal: subtotal,
            delivery_fee: fee,
            total_amount: subtotal + fee,
            items: this.state.cart.map(i => ({
                id: i.id,
                name: i.name,
                price: i.price,
                quantity: i.quantity,
                subtotal: i.price * i.quantity,
                options_summary: i.options_summary
            }))
        };

        // Si el método de pago es QR
        if (payload.payment_method === 'QR') {
            try {
                // Generar token único para identificar la orden
                const qrToken = 'QR-' + Math.random().toString(36).substr(2, 9).toUpperCase();
                payload.notes = (payload.notes ? payload.notes + ' ' : '') + `[QR_TOKEN:${qrToken}]`;

                // Codificar la payload
                const dataStr = JSON.stringify(payload);
                const base64Data = btoa(unescape(encodeURIComponent(dataStr)));

                // Obtener URL base de pay.php y reemplazar localhost/127.0.0.1 por la IP local del servidor
                let currentUrl = window.location.href;
                if (typeof SERVER_LOCAL_IP !== 'undefined' && SERVER_LOCAL_IP) {
                    currentUrl = currentUrl.replace('localhost', SERVER_LOCAL_IP).replace('127.0.0.1', SERVER_LOCAL_IP);
                }
                const basePath = currentUrl.substring(0, currentUrl.lastIndexOf('/'));
                const payUrl = `${basePath}/pay.php?data=${encodeURIComponent(base64Data)}`;

                // Cargar imagen de QR
                const qrCodeImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(payUrl)}`;

                this.dom.qrCodeContainer.innerHTML = `<img src="${qrCodeImgUrl}" alt="Código QR de Pago" style="max-width: 100%; height: auto;" />`;
                this.dom.qrTotalAmount.textContent = this.formatCurrency(payload.total_amount);

                // Cerrar checkout modal y abrir modal QR
                this.closeCheckoutModal();
                this.dom.qrPaymentModal.classList.add('active');

                // Iniciar Polling para detectar la confirmación de la orden en el móvil
                if (this.state.qrPollInterval) {
                    clearInterval(this.state.qrPollInterval);
                }

                this.state.qrPollInterval = setInterval(async () => {
                    try {
                        const checkRes = await fetch(`api/index.php?action=check_qr_token&token=${qrToken}`);
                        const checkData = await checkRes.json();

                        if (checkData.status === 'success' && checkData.order_code) {
                            // Limpiar intervalo y cerrar modal
                            this.closeQrPaymentModal();

                            // Limpiar carrito
                            this.state.cart = [];
                            this.saveCart();
                            this.updateCartUI();

                            // Mostrar éxito y abrir tracker
                            this.openTrackerModalWithCode(checkData.order_code);
                            this.showToast(`¡Pago recibido! Pedido #${checkData.order_code} generado.`, 'success');
                        }
                    } catch (pollErr) {
                        console.error('Error polling QR status:', pollErr);
                    }
                }, 3000);

            } catch (qrErr) {
                console.error('Error generando QR:', qrErr);
                this.showToast('Error al generar el código QR', 'error');
            }
            return;
        }

        try {
            const res = await fetch('api/index.php?action=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.status === 'success') {
                this.closeCheckoutModal();
                this.state.cart = [];
                this.saveCart();
                this.updateCartUI();

                // Mostrar éxito con código de pedido
                this.openTrackerModalWithCode(data.order_code);
                this.showToast(`¡Pedido #${data.order_code} realizado con éxito!`, 'success');
            } else {
                this.showToast(data.message || 'Error al procesar pedido', 'error');
            }
        } catch (err) {
            console.error(err);
            this.showToast('Error conectando con el servidor', 'error');
        }
    },

    openTrackerModal() {
        this.dom.trackerModal.classList.add('active');
    },

    openTrackerModalWithCode(code) {
        this.dom.trackerInput.value = code;
        this.openTrackerModal();
        this.fetchOrderStatus(code);
    },

    closeTrackerModal() {
        this.dom.trackerModal.classList.remove('active');
    },

    handleTrackerSubmit(e) {
        e.preventDefault();
        const code = this.dom.trackerInput.value.trim();
        if (code) {
            this.fetchOrderStatus(code);
        }
    },

    async fetchOrderStatus(code) {
        this.dom.trackerResult.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Buscando pedido...</p>';

        try {
            const res = await fetch(`api/index.php?action=order_status&code=${encodeURIComponent(code)}`);
            const data = await res.json();

            if (data.status === 'success' && data.order) {
                const order = data.order;
                const statusMap = {
                    pending: { text: 'Pendiente de Confirmación', color: '#ffb142', step: 1 },
                    preparing: { text: 'En la Plancha / Preparando 🍔', color: '#3498db', step: 2 },
                    ready: { text: '¡Listo para Entrega / En Camino! 🛵', color: '#2ecc71', step: 3 },
                    completed: { text: 'Entregado 🎉', color: '#95a5a6', step: 4 },
                    cancelled: { text: 'Cancelado ❌', color: '#e74c3c', step: 0 }
                };

                const current = statusMap[order.status] || statusMap['pending'];

                let itemsHtml = order.items.map(i => `
                    <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 4px;">
                        <span>${i.quantity}x ${i.product_name} ${i.options_summary ? `<small>(${i.options_summary})</small>` : ''}</span>
                        <strong>${this.formatCurrency(i.subtotal)}</strong>
                    </div>
                `).join('');

                this.dom.trackerResult.innerHTML = `
                    <div style="background: var(--bg-surface); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <strong style="font-size: 1.1rem;">Pedido #${order.order_code}</strong>
                            <span class="status-badge status-${order.status}">${current.text}</span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">Cliente: ${order.customer_name} | ${order.delivery_type === 'delivery' ? 'Envío a Domicilio' : 'Retiro en Local'}</p>
                        
                        <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-bottom: 12px;">
                            ${itemsHtml}
                        </div>

                        <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1rem; border-top: 1px dashed var(--border-color); padding-top: 10px;">
                            <span>Total</span>
                            <span>${this.formatCurrency(order.total_amount)}</span>
                        </div>
                    </div>
                `;
            } else {
                this.dom.trackerResult.innerHTML = `
                    <div style="text-align: center; color: var(--accent); padding: 20px;">
                        No se encontró ningún pedido con el código <strong>${code}</strong>.
                    </div>
                `;
            }
        } catch (err) {
            console.error(err);
            this.dom.trackerResult.innerHTML = '<p style="color: var(--accent);">Error al obtener el estado.</p>';
        }
    },

    showToast(message, type = 'info') {
        if (!this.dom.toastContainer) return;

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <span>${type === 'success' ? '✅' : type === 'error' ? '⚠️' : 'ℹ️'}</span>
            <span>${message}</span>
        `;

        this.dom.toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};
