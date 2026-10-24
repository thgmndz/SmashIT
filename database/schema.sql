-- Base de datos completa para SmashIT Burgers - Sistema ERP & Panel Administrativo

-- ==========================================
-- 1. MODULO REFERENCIALES (Tablas Maestras)
-- ==========================================

-- Categorías de Productos
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Productos (Burgers, Sides, Bebidas, Postres)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    base_price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NULL,
    badge VARCHAR(50) NULL,
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Grupos de Opciones
CREATE TABLE IF NOT EXISTS option_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('radio', 'checkbox') DEFAULT 'radio',
    min_selection INT DEFAULT 0,
    max_selection INT DEFAULT 1,
    is_required TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ítems de Opciones
CREATE TABLE IF NOT EXISTS option_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_group_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price_modifier DECIMAL(10, 2) DEFAULT 0.00,
    is_default TINYINT(1) DEFAULT 0,
    FOREIGN KEY (option_group_id) REFERENCES option_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relación Producto - Grupo de Opciones
CREATE TABLE IF NOT EXISTS product_options (
    product_id INT NOT NULL,
    option_group_id INT NOT NULL,
    PRIMARY KEY (product_id, option_group_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (option_group_id) REFERENCES option_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Proveedores (Referencial Compras)
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruc_tax_id VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100) NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insumos y Materias Primas (Referencial Inventario / Costos)
CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL, -- kg, g, unidad, litros, etc.
    current_stock DECIMAL(10, 2) DEFAULT 0.00,
    min_stock DECIMAL(10, 2) DEFAULT 5.00,
    unit_cost DECIMAL(10, 2) DEFAULT 0.00,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recetas / Escandallo / Ficha Técnica (Relación Producto -> Insumos)
CREATE TABLE IF NOT EXISTS product_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity_required DECIMAL(10, 3) NOT NULL, -- Cantidad de insumo por unidad de producto
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Empleados y Usuarios
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'cajero', 'cocinero', 'delivery') DEFAULT 'cajero',
    phone VARCHAR(30) NULL,
    pin_code VARCHAR(10) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clientes
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL UNIQUE,
    ruc_ci VARCHAR(30) NULL,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    total_orders INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 2. MODULO DE COMPRAS (Insumos y Facturas de Proveedores)
-- ==========================================

-- Cabecera de Compras
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_code VARCHAR(30) NOT NULL UNIQUE,
    supplier_id INT NOT NULL,
    invoice_number VARCHAR(50) NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'received', 'cancelled') DEFAULT 'received',
    notes TEXT NULL,
    purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Detalle de Compras
CREATE TABLE IF NOT EXISTS purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit_cost DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 3. MODULO DE VENTAS & CAJA
-- ==========================================

-- Arqueo / Cierre de Caja por Turno
CREATE TABLE IF NOT EXISTS cash_registers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    opening_amount DECIMAL(10, 2) NOT NULL,
    closing_amount DECIMAL(10, 2) NULL,
    calculated_amount DECIMAL(10, 2) NULL,
    difference DECIMAL(10, 2) NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    opened_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pedidos y Ventas
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    cash_register_id INT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL,
    delivery_type ENUM('pickup', 'delivery') DEFAULT 'delivery',
    delivery_address TEXT NULL,
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Detalle de Pedidos
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    options_summary TEXT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- 4. MODULO DE FACTURACIÓN (Normas SET Paraguay)
-- ==========================================

-- Cabecera de Facturas
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NULL,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    billing_mode VARCHAR(30) NOT NULL,
    invoice_date DATE NOT NULL,
    `condition` VARCHAR(20) NOT NULL DEFAULT 'CONTADO',
    timbrado_no VARCHAR(30) NOT NULL,
    timbrado_expiry DATE NOT NULL,
    customer_id INT NOT NULL,
    sucursal VARCHAR(50) NOT NULL,
    notes TEXT NULL,
    total_exento DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_iva5 DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_iva10 DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'emitida',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Detalle de Facturas
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_rate INT NOT NULL DEFAULT 10,
    tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
