<?php
require_once __DIR__ . '/../models/OrdenCompraModel.php';
require_once __DIR__ . '/../models/PresupuestoCompraModel.php';
require_once __DIR__ . '/../models/ProveedorModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class OrdenCompraController {
    private OrdenCompraModel $model;
    private PresupuestoCompraModel $presupuestoModel;
    private ProveedorModel $proveedorModel;
    private ProductModel $productModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new OrdenCompraModel();
        $this->presupuestoModel = new PresupuestoCompraModel();
        $this->proveedorModel = new ProveedorModel();
        $this->productModel = new ProductModel();
    }

    public function index(): void {
        $ordenes = $this->model->getAll();
        $presupuestos = $this->presupuestoModel->getAll();
        $proveedores = $this->proveedorModel->getAll();
        $products = $this->productModel->getAll();
        
        // Fetch employees
        $db = CRMDatabase::getConnection();
        $funcionarios = $db->query("SELECT id, name FROM employees ORDER BY name ASC")->fetchAll();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $referencia_presupuesto = trim($_POST['referencia_presupuesto'] ?? '');
                $presupuesto_compra_id = $_POST['presupuesto_compra_id'] !== '' ? (int)$_POST['presupuesto_compra_id'] : null;
                $fecha = trim($_POST['fecha'] ?? '2026-08-12');
                $sucursal = trim($_POST['sucursal'] ?? '');
                $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
                $funcionario_id = (int)($_POST['funcionario_id'] ?? 0);
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 1);
                $costo_unit = (float)($_POST['costo_unit'] ?? 0);

                if (empty($sucursal) || empty($proveedor_id) || empty($funcionario_id) || empty($producto) || $costo_unit <= 0) {
                    $error = 'Sucursal, Proveedor, Funcionario, Producto y Costo Unitario son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'referencia_presupuesto' => $referencia_presupuesto,
                        'presupuesto_compra_id' => $presupuesto_compra_id,
                        'fecha' => $fecha,
                        'sucursal' => $sucursal,
                        'proveedor_id' => $proveedor_id,
                        'funcionario_id' => $funcionario_id,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'costo_unit' => $costo_unit
                    ]);
                    if ($result) {
                        $success = 'Orden de compra registrada correctamente.';
                        $ordenes = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar la orden de compra.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $referencia_presupuesto = trim($_POST['referencia_presupuesto'] ?? '');
                $presupuesto_compra_id = $_POST['presupuesto_compra_id'] !== '' ? (int)$_POST['presupuesto_compra_id'] : null;
                $fecha = trim($_POST['fecha'] ?? '2026-08-12');
                $sucursal = trim($_POST['sucursal'] ?? '');
                $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
                $funcionario_id = (int)($_POST['funcionario_id'] ?? 0);
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 1);
                $costo_unit = (float)($_POST['costo_unit'] ?? 0);

                if (empty($sucursal) || empty($proveedor_id) || empty($funcionario_id) || empty($producto) || $costo_unit <= 0) {
                    $error = 'Sucursal, Proveedor, Funcionario, Producto y Costo Unitario son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'referencia_presupuesto' => $referencia_presupuesto,
                        'presupuesto_compra_id' => $presupuesto_compra_id,
                        'fecha' => $fecha,
                        'sucursal' => $sucursal,
                        'proveedor_id' => $proveedor_id,
                        'funcionario_id' => $funcionario_id,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'costo_unit' => $costo_unit
                    ]);
                    if ($result) {
                        $success = 'Orden de compra actualizada correctamente.';
                        $ordenes = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar la orden de compra.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Orden de compra eliminada correctamente.';
                    $ordenes = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar la orden de compra.';
                }
            }
        }

        require_once __DIR__ . '/../views/ordenes_compra/index.php';
    }
}
