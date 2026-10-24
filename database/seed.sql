-- Semilla de Datos Completa para SmashIT Burgers (Ventas, Compras, Referenciales)

DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM cash_registers;
DELETE FROM purchase_items;
DELETE FROM purchases;
DELETE FROM customers;
DELETE FROM employees;
DELETE FROM product_recipes;
DELETE FROM ingredients;
DELETE FROM suppliers;
DELETE FROM product_options;
DELETE FROM option_items;
DELETE FROM option_groups;
DELETE FROM products;
DELETE FROM categories;

-- ==========================================
-- 1. REFERENCIALES: CATEGORÍAS & PRODUCTOS
-- ==========================================

INSERT INTO categories (id, name, slug, description, sort_order) VALUES
(1, 'Smash Burgers', 'burgers', 'Doble o triple carne smash a la plancha de hierro fundido con queso cheddar derretido.', 1),
(2, 'Acompañamientos & Sides', 'sides', 'Papas crujientes, aros de cebolla y bites de pollo.', 2),
(3, 'Bebidas', 'drinks', 'Gaseosas, agua, cervezas y chopp artesanal bien fríos.', 3),
(4, 'Postres & Extras', 'desserts', 'Milkshakes cremosos, sundaes y salsas de la casa.', 4);

-- Grupos de opciones
INSERT INTO option_groups (id, name, type, min_selection, max_selection, is_required) VALUES
(1, '¡Hacelo Combo!', 'checkbox', 0, 1, 0),
(2, 'Punto de la Carne', 'radio', 1, 1, 1),
(3, 'Sabor de Gaseosa', 'radio', 1, 1, 1),
(4, 'Tipo de Agua', 'radio', 1, 1, 1),
(5, 'Marca de Cerveza', 'radio', 1, 1, 1),
(6, 'Estilo de Chopp', 'radio', 1, 1, 1),
(7, 'Sabor de Milkshake', 'radio', 1, 1, 1),
(8, 'Elección de Salsa Extra', 'radio', 1, 1, 1);

-- Ítems de opciones
INSERT INTO option_items (id, option_group_id, name, price_modifier, is_default) VALUES
(1, 1, 'Papas Fritas Medianas + Gaseosa 500ml', 15000.00, 0),
(2, 2, 'Jugosa / Término Medio', 0.00, 1),
(3, 2, 'Bien Cocida / Crujiente Smash', 0.00, 0),
(4, 3, 'Coca-Cola 500ml', 0.00, 1),
(5, 3, 'Sprite 500ml', 0.00, 0),
(6, 3, 'Fanta 500ml', 0.00, 0),
(7, 4, 'Sin Gas 500ml', 0.00, 1),
(8, 4, 'Con Gas 500ml', 0.00, 0),
(9, 5, 'Heineken 473ml', 0.00, 1),
(10, 5, 'Pilsen 473ml', 0.00, 0),
(11, 5, 'Munich Ultra 473ml', 0.00, 0),
(12, 6, 'IPA 500ml', 0.00, 1),
(13, 6, 'Pilsen de la Casa 500ml', 0.00, 0),
(14, 7, 'Oreo 400ml', 0.00, 1),
(15, 7, 'Chocolate 400ml', 0.00, 0),
(16, 7, 'Dulce de Leche 400ml', 0.00, 0),
(17, 8, 'Salsa SmashIT 50ml', 0.00, 1),
(18, 8, 'Queso Cheddar 50ml', 0.00, 0),
(19, 8, 'Alioli 50ml', 0.00, 0),
(20, 8, 'Salsa BBQ 50ml', 0.00, 0);

-- Productos
INSERT INTO products (id, category_id, name, slug, description, base_price, image_url, badge, is_featured) VALUES
(1, 1, 'Classic IT (Doble)', 'classic-it-doble', 'Doble carne smash, doble queso cheddar, pepinillos y salsa secreta SmashIT.', 32000.00, 'assets/images/classic-smash.jpg', 'TOP SELLER', 1),
(2, 1, 'Bacon Smash IT', 'bacon-smash-it', 'Doble carne smash, doble cheddar, panceta ahumada crujiente y salsa BBQ casera.', 36000.00, 'assets/images/bacon-smash.jpg', 'FAVORITA', 1),
(3, 1, 'Onion Smash', 'onion-smash', 'Doble carne smash aplastada con fina cebolla morada, queso cheddar y alioli.', 34000.00, 'assets/images/spicy-smash.jpg', 'NUEVO', 1),
(4, 1, 'Triple Threat IT', 'triple-threat-it', 'Triple carne smash, triple queso cheddar, panceta picada y salsa SmashIT.', 42000.00, 'assets/images/truffle-smash.jpg', '🔥 TRIPLE', 1),
(5, 2, 'Papas Clásicas', 'papas-clasicas', 'Papas bastón crujientes sazonadas con sal marina.', 15000.00, 'assets/images/fries.jpg', NULL, 0),
(6, 2, 'Papas Bacon & Cheese', 'papas-bacon-cheese', 'Bañadas en queso cheddar derretido y panceta crujiente.', 25000.00, 'assets/images/loaded-fries.jpg', 'POPULAR', 1),
(7, 2, 'Onion Rings (8 u.)', 'onion-rings-8u', 'Aros de cebolla empanados con salsa BBQ.', 20000.00, 'assets/images/fries.jpg', NULL, 0),
(8, 2, 'Chicken Bites (6 u.)', 'chicken-bites-6u', 'Bocadillos de pollo crujientes con honey mustard.', 22000.00, 'assets/images/fries.jpg', NULL, 0),
(9, 3, 'Gaseosa 500ml', 'gaseosa-500ml', 'Coca-Cola, Sprite, Fanta', 8000.00, 'assets/images/fries.jpg', NULL, 0),
(10, 3, 'Agua Mineral 500ml', 'agua-mineral-500ml', 'Con o sin gas', 6000.00, 'assets/images/fries.jpg', NULL, 0),
(11, 3, 'Cerveza en Lata 473ml', 'cerveza-en-lata-473ml', 'Heineken, Pilsen, Munich Ultra', 12000.00, 'assets/images/fries.jpg', NULL, 0),
(12, 3, 'Chopp Artesanal 500ml', 'chopp-artesanal-500ml', 'IPA / Pilsen de la casa', 18000.00, 'assets/images/fries.jpg', NULL, 0),
(13, 4, 'Smash Milkshake 400ml', 'smash-milkshake-400ml', 'Oreo, Chocolate o Dulce de Leche', 20000.00, 'assets/images/fries.jpg', 'DULCE', 1),
(14, 4, 'Sundae Crunchy', 'sundae-crunchy', 'Helado de vainilla, topping y sirope', 12000.00, 'assets/images/fries.jpg', NULL, 0),
(15, 4, 'Salsas Extra (50ml)', 'salsas-extra-50ml', 'SmashIT, Cheddar, Alioli, BBQ', 3000.00, 'assets/images/fries.jpg', NULL, 0);

-- Vinculación de opciones
INSERT INTO product_options (product_id, option_group_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2),
(3, 1), (3, 2),
(4, 1), (4, 2),
(9, 3), (10, 4), (11, 5), (12, 6), (13, 7), (15, 8);

-- ==========================================
-- 2. REFERENCIALES: PROVEEDORES, INSUMOS & EMPLEADOS
-- ==========================================

-- Proveedores
INSERT INTO suppliers (id, ruc_tax_id, name, contact_name, phone, email, address) VALUES
(1, '80012345-1', 'Frigorífico Concepción S.A.', 'Carlos Benítez', '0981 111 222', 'ventas@concepcion.com.py', 'Av. Artigas 1234, Asunción'),
(2, '80054321-2', 'Panadería Artesanal Brioche', 'María Giménez', '0982 333 444', 'pedidos@brioche.com.py', 'Calle Palma 456, Asunción'),
(3, '80098765-3', 'Lácteos & Quesos del Sur', 'Roberto Gómez', '0983 555 666', 'contacto@lacteossur.com.py', 'Av. Eusebio Ayala 789'),
(4, '80011223-4', 'Distribuidora de Bebidas Py', 'Jorge Silva', '0984 777 888', 'ventas@bebidaspy.com.py', 'Av. Mariscal López 321'),
(5, '80044556-5', 'Empaques & Cajas Smash S.R.L.', 'Ana Martínez', '0985 999 000', 'empaques@smash.com.py', 'Ruta Transchaco km 9');

-- Insumos / Materias Primas
INSERT INTO ingredients (id, code, name, unit, current_stock, min_stock, unit_cost) VALUES
(1, 'INS-001', 'Medalla de Carne Novillo (80g)', 'Unidad', 250.00, 50.00, 3500.00),
(2, 'INS-002', 'Pan Brioche Artesanal', 'Unidad', 120.00, 30.00, 2000.00),
(3, 'INS-003', 'Feta Queso Cheddar', 'Unidad', 300.00, 60.00, 1200.00),
(4, 'INS-004', 'Panceta Ahumada Crispy', 'Kg', 15.00, 3.00, 45000.00),
(5, 'INS-005', 'Pepinillos Encurtidos', 'Kg', 8.00, 2.00, 25000.00),
(6, 'INS-006', 'Salsa Secreta SmashIT', 'Litro', 12.00, 3.00, 18000.00),
(7, 'INS-007', 'Papas Fritas Congeladas (9x9)', 'Kg', 40.00, 10.00, 14000.00),
(8, 'INS-008', 'Aros de Cebolla Rebozados', 'Kg', 10.00, 3.00, 22000.00),
(9, 'INS-009', 'Chicken Bites Pechuga', 'Kg', 12.00, 4.00, 28000.00),
(10, 'INS-010', 'Caja Cartón SmashIT', 'Unidad', 400.00, 100.00, 1500.00);

-- Recetas / Escandallo (Ficha Técnica)
INSERT INTO product_recipes (product_id, ingredient_id, quantity_required) VALUES
-- Classic IT (Doble): 2 carne, 1 pan, 2 cheddar, 0.02kg pepinillos, 0.03L salsa, 1 caja
(1, 1, 2.00), (1, 2, 1.00), (1, 3, 2.00), (1, 5, 0.02), (1, 6, 0.03), (1, 10, 1.00),
-- Bacon Smash IT: 2 carne, 1 pan, 2 cheddar, 0.05kg panceta, 1 caja
(2, 1, 2.00), (2, 2, 1.00), (2, 3, 2.00), (2, 4, 0.05), (2, 10, 1.00),
-- Onion Smash: 2 carne, 1 pan, 1 cheddar, 1 caja
(3, 1, 2.00), (3, 2, 1.00), (3, 3, 1.00), (3, 10, 1.00),
-- Triple Threat: 3 carne, 1 pan, 3 cheddar, 0.05kg panceta, 1 caja
(4, 1, 3.00), (4, 2, 1.00), (4, 3, 3.00), (4, 4, 0.05), (4, 10, 1.00),
-- Papas Clásicas: 0.25kg papas
(5, 7, 0.25),
-- Papas Bacon & Cheese: 0.25kg papas, 2 cheddar, 0.04kg panceta
(6, 7, 0.25), (6, 3, 2.00), (6, 4, 0.04);

-- Empleados
INSERT INTO employees (id, document_id, name, role, phone, pin_code) VALUES
(1, '4567890', 'Thiago Mendez (Admin)', 'admin', '0981 999 888', '1234'),
(2, '5123456', 'Lucas Peralta (Cajero)', 'cajero', '0982 777 666', '2024'),
(3, '3890123', 'Gonzalo Rojas (Parrillero)', 'cocinero', '0983 444 555', '9999');

-- Clientes
INSERT INTO customers (id, name, phone, email, address, total_orders) VALUES
(1, 'Rodrigo Almada', '0981 123 456', 'rodrigo@gmail.com', 'Calle España 1020, Asunción', 5),
(2, 'Camila Duarte', '0982 654 321', 'camila.d@hotmail.com', 'Av. Santa Teresa 450', 3),
(3, 'Esteban Cabrera', '0983 112 233', 'esteban.c@yahoo.com', 'Av. Molas López 789', 2);

-- ==========================================
-- 3. MÓDULO DE COMPRAS (Órdenes a Proveedores)
-- ==========================================

INSERT INTO purchases (id, purchase_code, supplier_id, invoice_number, total_amount, status, notes, purchased_at) VALUES
(1, 'COM-2026-001', 1, 'FC-001-9982', 875000.00, 'received', 'Compra semanal de medallas de carne 80g novillo', '2026-08-01 10:30:00'),
(2, 'COM-2026-002', 2, 'PA-002-4410', 240000.00, 'received', 'Pan brioche fresco 120 unidades', '2026-08-02 08:15:00'),
(3, 'COM-2026-003', 3, 'LS-001-1102', 360000.00, 'received', 'Lote de fetas queso cheddar 300u', '2026-08-02 11:00:00');

INSERT INTO purchase_items (id, purchase_id, ingredient_id, quantity, unit_cost, subtotal) VALUES
(1, 1, 1, 250.00, 3500.00, 875000.00),
(2, 2, 2, 120.00, 2000.00, 24000.00),
(3, 3, 3, 300.00, 1200.00, 360000.00);

-- ==========================================
-- 4. MÓDULO DE VENTAS & ARQUEO DE CAJA
-- ==========================================

-- Arqueo de Caja Abierto hoy
INSERT INTO cash_registers (id, employee_id, opening_amount, closing_amount, calculated_amount, difference, status, opened_at) VALUES
(1, 2, 200000.00, NULL, 534000.00, 0.00, 'open', '2026-08-03 18:00:00');

-- Ventas de prueba
INSERT INTO orders (id, order_code, cash_register_id, customer_name, customer_email, customer_phone, delivery_type, delivery_address, payment_method, subtotal, delivery_fee, total_amount, status, notes, created_at) VALUES
(1, 'SMASH-78A1', 1, 'Rodrigo Almada', 'rodrigo@gmail.com', '0981 123 456', 'delivery', 'Calle España 1020, Asunción', 'Transferencia / Billetera', 64000.00, 10000.00, 74000.00, 'completed', 'Sin pepinillos', '2026-08-03 19:10:00'),
(2, 'SMASH-92B4', 1, 'Camila Duarte', 'camila.d@hotmail.com', '0982 654 321', 'pickup', 'Retiro en Local', 'Efectivo al Entregar', 51000.00, 0.00, 51000.00, 'preparing', 'Papas extra crujientes', '2026-08-03 19:45:00'),
(3, 'SMASH-10C3', 1, 'Esteban Cabrera', 'esteban.c@yahoo.com', '0983 112 233', 'delivery', 'Av. Molas López 789', 'Tarjeta POS al Entregar', 42000.00, 10000.00, 52000.00, 'pending', 'Timbrar fuerte', '2026-08-03 20:15:00');

INSERT INTO order_items (id, order_id, product_id, product_name, unit_price, quantity, subtotal, options_summary) VALUES
(1, 1, 1, 'Classic IT (Doble)', 32000.00, 2, 64000.00, 'Jugosa / Término Medio'),
(2, 2, 2, 'Bacon Smash IT', 36000.00, 1, 36000.00, 'Bien Cocida'),
(3, 2, 5, 'Papas Clásicas', 15000.00, 1, 15000.00, ''),
(4, 3, 4, 'Triple Threat IT', 42000.00, 1, 42000.00, 'Jugosa');
