const ThemeManager = {
    init: function() {
        const theme = this.getCookie('crm-theme') || 'dark';
        if (theme === 'light') {
            document.body.classList.add('light-theme');
            this.updateButton(true);
        } else {
            document.body.classList.remove('light-theme');
            this.updateButton(false);
        }
    },
    toggle: function() {
        const isLight = document.body.classList.toggle('light-theme');
        this.setCookie('crm-theme', isLight ? 'light' : 'dark', 365);
        this.updateButton(isLight);
    },
    updateButton: function(isLight) {
        const btn = document.getElementById('btn-theme-toggle');
        if (btn) {
            if (isLight) {
                btn.innerHTML = '<i class="fa fa-sun me-1"></i> Modo Claro';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-outline-warning');
            } else {
                btn.innerHTML = '<i class="fa fa-moon me-1"></i> Modo Oscuro';
                btn.classList.remove('btn-outline-warning');
                btn.classList.add('btn-outline-secondary');
            }
        }
    },
    setCookie: function(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    },
    getCookie: function(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i=0;i < ca.length;i++) {
            let c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
};

const ColorCustomizer = {
    init: function() {
        const primary = localStorage.getItem('custom-primary') || '#e28743';
        const secondary = localStorage.getItem('custom-secondary') || '#111111';
        document.documentElement.style.setProperty('--primary-color', primary);
        document.documentElement.style.setProperty('--secondary-color', secondary);
        
        // Update picker values if they are loaded
        const primaryInput = document.getElementById('custom-primary-picker');
        const secondaryInput = document.getElementById('custom-secondary-picker');
        if (primaryInput) primaryInput.value = primary;
        if (secondaryInput) secondaryInput.value = secondary;
    },
    setPrimary: function(val) {
        document.documentElement.style.setProperty('--primary-color', val);
        localStorage.setItem('custom-primary', val);
    },
    setSecondary: function(val) {
        document.documentElement.style.setProperty('--secondary-color', val);
        localStorage.setItem('custom-secondary', val);
    },
    reset: function() {
        localStorage.removeItem('custom-primary');
        localStorage.removeItem('custom-secondary');
        location.reload();
    }
};

document.addEventListener('DOMContentLoaded', function () {
    ThemeManager.init();
    ColorCustomizer.init();

    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleBtn = document.getElementById('sidebarCollapse');

    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('show');
            mainContent.classList.toggle('expanded');
        });
    }

    // Auto-close messages alert
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 4000);

    // Auto-open print tab on confirm payment click (bypasses browser popup blocker)
    const confirmPaymentBtn = document.getElementById('confirm_payment_btn');
    if (confirmPaymentBtn) {
        confirmPaymentBtn.addEventListener('click', function () {
            const selectType = document.getElementById('invoice_type_select').value;
            const orderId = document.getElementById('payment_order_id').value;
            if (orderId) {
                // Abrimos la pestaña inmediatamente en respuesta al click
                window.open('print.php?id=' + orderId + '&type=' + selectType, '_blank');
            }
        });
    }

    // Auto-open print tab on reprint confirmation click
    const confirmReprintBtn = document.getElementById('confirm_reprint_btn');
    if (confirmReprintBtn) {
        confirmReprintBtn.addEventListener('click', function () {
            const selectType = document.getElementById('reprint_type_select').value;
            const orderId = document.getElementById('reprint_order_id').value;
            if (orderId) {
                window.open('print.php?id=' + orderId + '&type=' + selectType, '_blank');
                
                // Cerrar modal tras la impresión
                const modalEl = document.getElementById('reprintModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        });
    }
});

// Helper functions for filling Modals
function openEditCustomerModal(id, name, phone, rucCi, email, address, totalOrders) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_ruc_ci').value = rucCi;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_total_orders').value = totalOrders;

    const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    modal.show();
}

function openDeleteCustomerModal(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_customer_name').textContent = name;

    const modal = new bootstrap.Modal(document.getElementById('deleteCustomerModal'));
    modal.show();
}

function openEditProductModal(id, name, price, isAvailable, badge) {
    document.getElementById('product_edit_id').value = id;
    document.getElementById('product_edit_name').value = name;
    document.getElementById('product_edit_price').value = price;
    document.getElementById('product_edit_available').checked = (isAvailable == 1);
    
    const badgeInput = document.getElementById('product_edit_badge');
    if (badgeInput) {
        badgeInput.value = badge || '';
    }

    const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
    modal.show();
}

function openOrderDetailsModal(orderCode, customerName, phone, email, type, address, payment, subtotal, deliveryFee, total, status, notes, itemsJson) {
    document.getElementById('detail_order_code').innerText = orderCode;
    document.getElementById('detail_customer_name').innerText = customerName;
    document.getElementById('detail_customer_phone').innerText = phone;
    document.getElementById('detail_customer_email').innerText = email || 'No asignado';
    
    let deliveryTypeText = 'Consumo en Local 🍽️';
    if (type === 'delivery') {
        deliveryTypeText = 'Delivery 🚴';
    } else if (type === 'pickup') {
        deliveryTypeText = 'Retiro en Local 🛍️';
    }
    document.getElementById('detail_delivery_type').innerText = deliveryTypeText;
    let addressHtml = address || 'N/A';
    if (address && address.includes('http')) {
        const urlRegex = /(https?:\/\/[^\s\]]+)/g;
        addressHtml = address.replace(urlRegex, '<a href="$1" target="_blank" class="btn btn-sm btn-warning text-dark ms-2 fw-bold" style="padding: 2px 8px; font-size: 0.75rem; border-radius: 6px;"><i class="fa fa-map-marked-alt"></i> Ver en Mapa 📍</a>');
    }
    document.getElementById('detail_delivery_address').innerHTML = addressHtml;
    document.getElementById('detail_payment_method').innerText = payment;
    document.getElementById('detail_subtotal').innerText = parseFloat(subtotal).toLocaleString() + ' Gs.';
    document.getElementById('detail_delivery_fee').innerText = parseFloat(deliveryFee).toLocaleString() + ' Gs.';
    document.getElementById('detail_total').innerText = parseFloat(total).toLocaleString() + ' Gs.';
    let cleanNotes = notes || 'Ninguna';
    if (notes) {
        cleanNotes = notes.replace(/\[QR_TOKEN:[^\]]+\]/g, '').trim() || 'Ninguna';
    }
    document.getElementById('detail_notes').innerText = cleanNotes;

    // Status styling badge
    let statusClass = 'bg-secondary';
    if (status === 'pending') statusClass = 'bg-warning text-dark';
    else if (status === 'preparing') statusClass = 'bg-info text-dark';
    else if (status === 'ready') statusClass = 'bg-primary';
    else if (status === 'completed') statusClass = 'bg-success';
    else if (status === 'cancelled') statusClass = 'bg-danger';
    
    document.getElementById('detail_status').innerHTML = `<span class="badge ${statusClass}">${status.toUpperCase()}</span>`;

    // Render items list
    let itemsHtml = '';
    try {
        const items = JSON.parse(itemsJson);
        if (items && items.length > 0) {
            items.forEach(function(item) {
                itemsHtml += `
                    <tr>
                        <td>${item.product_name} <small class="text-muted d-block">${item.options_summary || ''}</small></td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${parseFloat(item.unit_price).toLocaleString()} Gs.</td>
                        <td class="text-end">${parseFloat(item.subtotal).toLocaleString()} Gs.</td>
                    </tr>
                `;
            });
        } else {
            itemsHtml = '<tr><td colspan="4" class="text-center text-muted">No hay ítems en este pedido</td></tr>';
        }
    } catch(e) {
        itemsHtml = '<tr><td colspan="4" class="text-center text-muted">Error al leer los detalles de los productos</td></tr>';
    }

    document.getElementById('detail_items_body').innerHTML = itemsHtml;

    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
}

function openUpdateOrderStatusModal(id, currentStatus) {
    document.getElementById('status_order_id').value = id;
    document.getElementById('status_select').value = currentStatus;

    const modal = new bootstrap.Modal(document.getElementById('updateOrderStatusModal'));
    modal.show();
}

function openEditTableModal(id, tableNumber, status, orderCode, resName, resCi, resDate, resTime) {
    document.getElementById('table_edit_id').value = id;
    document.getElementById('table_edit_number').textContent = tableNumber;
    document.getElementById('table_edit_status').value = status;
    document.getElementById('table_edit_order_code').value = orderCode;

    const nameInput = document.getElementById('table_edit_res_name');
    const ciInput = document.getElementById('table_edit_res_ci');
    const dateInput = document.getElementById('table_edit_res_date');
    const timeInput = document.getElementById('table_edit_res_time');

    if (nameInput) nameInput.value = resName || '';
    if (ciInput) ciInput.value = resCi || '';
    if (dateInput) dateInput.value = resDate || '';
    if (timeInput) timeInput.value = resTime || '';

    toggleReservationFields();

    const modal = new bootstrap.Modal(document.getElementById('editTableModal'));
    modal.show();
}

function toggleReservationFields() {
    const statusSelect = document.getElementById('table_edit_status');
    const fields = document.getElementById('reservationFields');
    if (statusSelect && fields) {
        if (statusSelect.value === 'reservado') {
            fields.style.display = 'block';
            document.getElementById('table_edit_res_name').required = true;
            document.getElementById('table_edit_res_ci').required = true;
            document.getElementById('table_edit_res_date').required = true;
            document.getElementById('table_edit_res_time').required = true;
        } else {
            fields.style.display = 'none';
            document.getElementById('table_edit_res_name').required = false;
            document.getElementById('table_edit_res_ci').required = false;
            document.getElementById('table_edit_res_date').required = false;
            document.getElementById('table_edit_res_time').required = false;
        }
    }
}

function openDeleteTableModal(id, tableNumber) {
    document.getElementById('delete_table_id').value = id;
    document.getElementById('delete_table_number').textContent = tableNumber;

    const modal = new bootstrap.Modal(document.getElementById('deleteTableModal'));
    modal.show();
}

function openTakeOrderModal(id, tableNumber) {
    document.getElementById('take_order_table_id').value = id;
    document.getElementById('take_order_table_number').textContent = tableNumber;
    document.getElementById('take_order_table_number_val').value = tableNumber;
    
    const customerSelect = document.getElementById('take_order_customer_select');
    if (customerSelect) customerSelect.value = 'new';
    
    const nameInput = document.getElementById('take_order_customer_name');
    if (nameInput) {
        nameInput.value = '';
        nameInput.readOnly = false;
    }
    
    const phoneInput = document.getElementById('take_order_customer_phone');
    if (phoneInput) {
        phoneInput.value = '';
        phoneInput.readOnly = false;
        phoneInput.required = true;
    }
    
    document.getElementById('take_order_ruc').value = '';
    document.getElementById('take_order_payment').value = 'Efectivo';
    
    const qtyInputs = document.querySelectorAll('#takeOrderModal input[type="number"]');
    qtyInputs.forEach(input => input.value = 0);

    const modal = new bootstrap.Modal(document.getElementById('takeOrderModal'));
    modal.show();
}

function openPaymentModal(orderId, orderCode, customerName, totalAmount, paymentMethod, itemsJson) {
    document.getElementById('payment_order_id').value = orderId;
    document.getElementById('payment_order_code').textContent = orderCode;
    document.getElementById('payment_customer_name').textContent = customerName;
    document.getElementById('payment_total_display').textContent = parseFloat(totalAmount).toLocaleString() + ' Gs.';
    document.getElementById('payment_method_select').value = paymentMethod;

    let itemsHtml = '';
    try {
        const items = JSON.parse(itemsJson);
        if (items && items.length > 0) {
            items.forEach(function(item) {
                itemsHtml += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${parseFloat(item.subtotal).toLocaleString()} Gs.</td>
                    </tr>
                `;
            });
        }
    } catch(e) {
        itemsHtml = '<tr><td colspan="3" class="text-center text-muted">Error al cargar productos</td></tr>';
    }

    document.getElementById('payment_items_body').innerHTML = itemsHtml;

    const modal = new bootstrap.Modal(document.getElementById('processPaymentModal'));
    modal.show();
}

function openCloseRegisterModal(registerId, calculatedAmount) {
    document.getElementById('close_register_id').value = registerId;
    document.getElementById('close_calculated_amount').value = calculatedAmount;
    document.getElementById('closing_amount').value = calculatedAmount;

    const modal = new bootstrap.Modal(document.getElementById('closeRegisterModal'));
    modal.show();
}

function openReprintModal(orderId, orderCode) {
    document.getElementById('reprint_order_id').value = orderId;
    document.getElementById('reprint_order_code').textContent = orderCode;
    const modal = new bootstrap.Modal(document.getElementById('reprintModal'));
    modal.show();
}


