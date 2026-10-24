<?php
require_once __DIR__ . '/config/db.php';

try {
    $db = CRMDatabase::getConnection();

    // 1. Crear tabla proveedores
    $db->exec("CREATE TABLE IF NOT EXISTS proveedores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        ruc VARCHAR(50) NOT NULL,
        telefono VARCHAR(50) NOT NULL,
        categoria_producto VARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'proveedores' creada o ya existente.\n";

    // 2. Crear tabla pedidos_compra
    $db->exec("CREATE TABLE IF NOT EXISTS pedidos_compra (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sucursal VARCHAR(100) NOT NULL,
        observacion TEXT NULL,
        producto VARCHAR(255) NOT NULL,
        cantidad INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'pedidos_compra' creada o ya existente.\n";

    // 3. Crear tabla presupuestos_compra
    $db->exec("CREATE TABLE IF NOT EXISTS presupuestos_compra (
        id INT AUTO_INCREMENT PRIMARY KEY,
        referencia_pedido VARCHAR(100) NULL,
        pedido_compra_id INT NULL,
        proveedor_id INT NOT NULL,
        sucursal VARCHAR(100) NOT NULL,
        fecha_vencimiento DATE DEFAULT '2026-08-27',
        observacion TEXT NULL,
        producto VARCHAR(255) NOT NULL,
        cantidad INT DEFAULT 1,
        costo_unit DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pedido_compra_id) REFERENCES pedidos_compra(id) ON DELETE SET NULL,
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'presupuestos_compra' creada o ya existente.\n";

    // 4. Crear tabla ordenes_compra
    $db->exec("CREATE TABLE IF NOT EXISTS ordenes_compra (
        id INT AUTO_INCREMENT PRIMARY KEY,
        referencia_presupuesto VARCHAR(100) NULL,
        presupuesto_compra_id INT NULL,
        fecha DATE DEFAULT '2026-08-12',
        sucursal VARCHAR(100) NOT NULL,
        proveedor_id INT NOT NULL,
        funcionario_id INT NOT NULL,
        observacion TEXT NULL,
        producto VARCHAR(255) NOT NULL,
        cantidad INT DEFAULT 1,
        costo_unit DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (presupuesto_compra_id) REFERENCES presupuestos_compra(id) ON DELETE SET NULL,
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE,
        FOREIGN KEY (funcionario_id) REFERENCES employees(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'ordenes_compra' creada o ya existente.\n";

    // 5. Crear tabla facturas_compra
    $db->exec("CREATE TABLE IF NOT EXISTS facturas_compra (
        id INT AUTO_INCREMENT PRIMARY KEY,
        referencia_orden_compra VARCHAR(100) NULL,
        orden_compra_id INT NULL,
        fecha DATE DEFAULT '2026-08-12',
        condicion ENUM('CONTADO', 'CRÉDITO') DEFAULT 'CONTADO',
        numero_factura VARCHAR(50) DEFAULT '001-001-0000001',
        nro_timbrado VARCHAR(50) NOT NULL,
        vencimiento_timbrado DATE DEFAULT '2026-08-12',
        proveedor_id INT NOT NULL,
        sucursal VARCHAR(100) NOT NULL,
        observacion TEXT NULL,
        producto VARCHAR(255) NOT NULL,
        cantidad INT DEFAULT 1,
        costo_unit DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE SET NULL,
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'facturas_compra' creada o ya existente.\n";

    // 6. Crear tabla arqueos_caja
    $db->exec("CREATE TABLE IF NOT EXISTS arqueos_caja (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cash_register_id INT NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        efectivo_declarado DECIMAL(10,2) NOT NULL,
        tarjeta_declarado DECIMAL(10,2) NOT NULL,
        cheque_declarado DECIMAL(10,2) NOT NULL,
        monto_sistema DECIMAL(10,2) NOT NULL,
        diferencia DECIMAL(10,2) NOT NULL,
        observacion TEXT NULL,
        FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'arqueos_caja' creada o ya existente.\n";

    // 7. Crear tabla recaudaciones_depositar
    $db->exec("CREATE TABLE IF NOT EXISTS recaudaciones_depositar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cash_register_id INT NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        monto_depositar DECIMAL(10,2) NOT NULL,
        banco VARCHAR(100) NOT NULL,
        nro_boleta VARCHAR(100) NOT NULL,
        observacion TEXT NULL,
        estado ENUM('pendiente', 'depositado') DEFAULT 'pendiente',
        FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'recaudaciones_depositar' creada o ya existente.\n";

    // 8. Crear tabla delivery_drivers
    $db->exec("CREATE TABLE IF NOT EXISTS delivery_drivers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        telefono VARCHAR(50) NOT NULL,
        vehiculo VARCHAR(100) NOT NULL,
        chapa VARCHAR(50) NOT NULL,
        activo TINYINT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "[+] Tabla 'delivery_drivers' creada o ya existente.\n";

    // 9. Alterar tabla orders para agregar delivery_driver_id y delivery_status si no existen
    try {
        $db->exec("ALTER TABLE orders ADD COLUMN delivery_driver_id INT NULL, ADD COLUMN delivery_status ENUM('pendiente', 'en_camino', 'entregado', 'devuelto') DEFAULT 'pendiente'");
        $db->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_delivery_driver FOREIGN KEY (delivery_driver_id) REFERENCES delivery_drivers(id) ON DELETE SET NULL");
        echo "[+] Columnas de delivery agregadas a la tabla 'orders'.\n";
    } catch (Exception $e) {
        echo "[~] Columnas de delivery en 'orders' ya existían o no pudieron agregarse.\n";
    }

    // Sembrar 10 choferes si está vacía
    $countDrivers = $db->query("SELECT COUNT(*) FROM delivery_drivers")->fetchColumn();
    if ($countDrivers == 0) {
        $drivers = [
            ['nombre' => 'Carlos Giménez', 'telefono' => '0981-222-333', 'vehiculo' => 'Motocicleta Kenton', 'chapa' => '123-AAA'],
            ['nombre' => 'Hugo Peralta', 'telefono' => '0982-333-444', 'vehiculo' => 'Motocicleta Kenton', 'chapa' => '456-BBB'],
            ['nombre' => 'Luis Cáceres', 'telefono' => '0983-444-555', 'vehiculo' => 'Motocicleta Taiga', 'chapa' => '789-CCC'],
            ['nombre' => 'Mateo Ortíz', 'telefono' => '0984-555-666', 'vehiculo' => 'Furgón Toyota', 'chapa' => '012-DDD'],
            ['nombre' => 'Diego Valdez', 'telefono' => '0985-666-777', 'vehiculo' => 'Motocicleta Kenton', 'chapa' => '345-EEE'],
            ['nombre' => 'Andrés Galeano', 'telefono' => '0986-777-888', 'vehiculo' => 'Motocicleta Taiga', 'chapa' => '678-FFF'],
            ['nombre' => 'Gustavo Benítez', 'telefono' => '0987-888-999', 'vehiculo' => 'Furgón Toyota', 'chapa' => '901-GGG'],
            ['nombre' => 'Ángel Ruíz', 'telefono' => '0988-999-000', 'vehiculo' => 'Motocicleta Kenton', 'chapa' => '234-HHH'],
            ['nombre' => 'Rodrigo Silva', 'telefono' => '0989-000-111', 'vehiculo' => 'Motocicleta Honda', 'chapa' => '567-III'],
            ['nombre' => 'Oscar Gómez', 'telefono' => '0991-111-222', 'vehiculo' => 'Motocicleta Kenton', 'chapa' => '890-JJJ']
        ];
        $stmt = $db->prepare("INSERT INTO delivery_drivers (nombre, telefono, vehiculo, chapa, activo) VALUES (:nombre, :telefono, :vehiculo, :chapa, 1)");
        foreach ($drivers as $d) {
            $stmt->execute($d);
        }
        echo "[+] 10 choferes de delivery sembrados.\n";
    }

    // Verificar si existen empleados, si no, creamos uno
    $empCount = $db->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    if ($empCount == 0) {
        $db->exec("INSERT INTO employees (document_id, name, role, phone, pin_code) VALUES ('123456', 'Juan Funcionario', 'admin', '0981123456', '1234')");
        echo "[+] Empleado temporal creado para clave foránea.\n";
    }
    
    // Obtener primer id de empleado para sembrar
    $employeeId = (int)$db->query("SELECT id FROM employees LIMIT 1")->fetchColumn();

    // Sembrar 10 cajas si está vacía
    $countReg = $db->query("SELECT COUNT(*) FROM cash_registers")->fetchColumn();
    if ($countReg == 0) {
        try {
            $stmtReg = $db->prepare("INSERT INTO cash_registers (employee_id, opening_amount, closing_amount, calculated_amount, difference, status, opened_at, closed_at) VALUES (:emp, :op, :cl, :ca, :di, :st, :op_at, :cl_at)");
            for ($i = 1; $i <= 10; $i++) {
                $stmtReg->execute([
                    ':emp' => $employeeId,
                    ':op' => 100000.00,
                    ':cl' => ($i < 10) ? (100000.00 + $i * 50000) : null,
                    ':ca' => ($i < 10) ? (100000.00 + $i * 50000) : null,
                    ':di' => ($i < 10) ? 0.00 : null,
                    ':st' => ($i < 10) ? 'closed' : 'open',
                    ':op_at' => date('Y-m-d H:i:s', strtotime("-$i days")),
                    ':cl_at' => ($i < 10) ? date('Y-m-d H:i:s', strtotime("-$i days + 8 hours")) : null
                ]);
            }
            echo "[+] 10 cajas registradas sembradas.\n";
        } catch (Exception $ex) {
            echo "[-] Error en sembrado de cajas: " . $ex->getMessage() . "\n";
        }
    }

    // Obtener todas las cajas existentes
    $registers = $db->query("SELECT id FROM cash_registers")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($registers)) {
        $db->exec("INSERT INTO cash_registers (employee_id, opening_amount, status) VALUES ($employeeId, 100000.00, 'open')");
        $registers = [$db->lastInsertId()];
    }

    // Sembrar 10 arqueos de caja
    $countArq = $db->query("SELECT COUNT(*) FROM arqueos_caja")->fetchColumn();
    if ($countArq == 0) {
        try {
            $stmtArq = $db->prepare("INSERT INTO arqueos_caja (cash_register_id, efectivo_declarado, tarjeta_declarado, cheque_declarado, monto_sistema, diferencia, observacion) VALUES (:cash_register_id, :efectivo_declarado, :tarjeta_declarado, :cheque_declarado, :monto_sistema, :diferencia, :observacion)");
            for ($i = 1; $i <= 10; $i++) {
                $regId = $registers[($i - 1) % count($registers)];
                $stmtArq->execute([
                    ':cash_register_id' => $regId,
                    ':efectivo_declarado' => 200000.00,
                    ':tarjeta_declarado' => 150000.00,
                    ':cheque_declarado' => 0.00,
                    ':monto_sistema' => 350000.00,
                    ':diferencia' => 0.00,
                    ':observacion' => "Arqueo de prueba día -$i"
                ]);
            }
            echo "[+] 10 arqueos de caja sembrados.\n";
        } catch (Exception $ex) {
            echo "[-] Error en sembrado de arqueos: " . $ex->getMessage() . "\n";
        }
    }

    // Sembrar 10 recaudaciones a depositar
    $countRec = $db->query("SELECT COUNT(*) FROM recaudaciones_depositar")->fetchColumn();
    if ($countRec == 0) {
        try {
            $stmtRec = $db->prepare("INSERT INTO recaudaciones_depositar (cash_register_id, monto_depositar, banco, nro_boleta, observacion, estado) VALUES (:cash_register_id, :monto_depositar, :banco, :nro_boleta, :observacion, :estado)");
            for ($i = 1; $i <= 10; $i++) {
                $regId = $registers[($i - 1) % count($registers)];
                $stmtRec->execute([
                    ':cash_register_id' => $regId,
                    ':monto_depositar' => 350000.00,
                    ':banco' => ($i % 2 === 0) ? 'Banco Itaú' : 'Banco Familiar',
                    ':nro_boleta' => "BOL-" . (100000 + $i),
                    ':observacion' => "Depósito de prueba -$i",
                    ':estado' => ($i < 10) ? 'depositado' : 'pendiente'
                ]);
            }
            echo "[+] 10 recaudaciones sembradas.\n";
        } catch (Exception $ex) {
            echo "[-] Error en sembrado de recaudaciones: " . $ex->getMessage() . "\n";
        }
    }

    // Sembrar 10 proveedores si está vacía
    $count = $db->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
    if ($count == 0) {
        try {
            $proveedores = [
                ['nombre' => 'Distribuidora Alimentos S.A.', 'ruc' => '1234567-1', 'telefono' => '0981-111-222', 'categoria_producto' => 'Carnes y Embutidos'],
                ['nombre' => 'Panadería San José', 'ruc' => '2345678-2', 'telefono' => '0982-222-333', 'categoria_producto' => 'Panificados'],
                ['nombre' => 'Bebidas del Norte S.R.L.', 'ruc' => '3456789-3', 'telefono' => '0983-333-444', 'categoria_producto' => 'Bebidas'],
                ['nombre' => 'Verdulería El Abasto', 'ruc' => '4567890-4', 'telefono' => '0984-444-555', 'categoria_producto' => 'Verduras y Hortalizas'],
                ['nombre' => 'Envases Paraguay', 'ruc' => '5678901-5', 'telefono' => '0985-555-666', 'categoria_producto' => 'Descartables y Packaging'],
                ['nombre' => 'Lácteos La Pradera', 'ruc' => '6789012-6', 'telefono' => '0986-666-777', 'categoria_producto' => 'Lácteos y Quesos'],
                ['nombre' => 'Distribuidora de Condimentos Goya', 'ruc' => '7890123-7', 'telefono' => '0987-777-888', 'categoria_producto' => 'Condimentos y Salsas'],
                ['nombre' => 'Importadora TecnoClean', 'ruc' => '8901234-8', 'telefono' => '0988-888-999', 'categoria_producto' => 'Productos de Limpieza'],
                ['nombre' => 'Gas Metalúrgica', 'ruc' => '9012345-9', 'telefono' => '0989-999-000', 'categoria_producto' => 'Gas y Mantenimiento'],
                ['nombre' => 'Papelera Central', 'ruc' => '0123456-0', 'telefono' => '0980-000-111', 'categoria_producto' => 'Artículos de Oficina']
            ];
            $stmt = $db->prepare("INSERT INTO proveedores (nombre, ruc, telefono, categoria_producto) VALUES (:nombre, :ruc, :telefono, :categoria_producto)");
            foreach ($proveedores as $p) {
                $stmt->execute($p);
            }
            echo "[+] 10 proveedores sembrados.\n";
        } catch (Exception $ex) {
            echo "[-] Error en sembrado de proveedores: " . $ex->getMessage() . "\n";
        }
    }

    echo "[+] Tablas de compras y cajas configuradas con éxito.\n";

} catch (Exception $e) {
    echo "[-] Error general: " . $e->getMessage() . "\n";
}
