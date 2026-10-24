<?php
require_once __DIR__ . '/../models/PresupuestoCompraModel.php';
require_once __DIR__ . '/../models/ProveedorModel.php';
require_once __DIR__ . '/../models/PedidoCompraModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class PresupuestoCompraController {
    private PresupuestoCompraModel $model;
    private ProveedorModel $proveedorModel;
    private PedidoCompraModel $pedidoModel;
    private ProductModel $productModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new PresupuestoCompraModel();
        $this->proveedorModel = new ProveedorModel();
        $this->pedidoModel = new PedidoCompraModel();
        $this->productModel = new ProductModel();
    }

    public function index(): void {
        $presupuestos = $this->model->getAll();
        $proveedores = $this->proveedorModel->getAll();
        $pedidos = $this->pedidoModel->getAll();
        $products = $this->productModel->getAll();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $referencia_pedido = trim($_POST['referencia_pedido'] ?? '');
                $pedido_compra_id = $_POST['pedido_compra_id'] !== '' ? (int)$_POST['pedido_compra_id'] : null;
                $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
                $sucursal = trim($_POST['sucursal'] ?? '');
                $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '2026-08-27');
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 1);
                $costo_unit = (float)($_POST['costo_unit'] ?? 0);

                if (empty($proveedor_id) || empty($sucursal) || empty($producto) || $costo_unit <= 0) {
                    $error = 'Proveedor, Sucursal, Producto y Costo Unitario son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'referencia_pedido' => $referencia_pedido,
                        'pedido_compra_id' => $pedido_compra_id,
                        'proveedor_id' => $proveedor_id,
                        'sucursal' => $sucursal,
                        'fecha_vencimiento' => $fecha_vencimiento,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'costo_unit' => $costo_unit
                    ]);
                    if ($result) {
                        $success = 'Presupuesto de compra registrado correctamente.';
                        $presupuestos = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar el presupuesto.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $referencia_pedido = trim($_POST['referencia_pedido'] ?? '');
                $pedido_compra_id = $_POST['pedido_compra_id'] !== '' ? (int)$_POST['pedido_compra_id'] : null;
                $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
                $sucursal = trim($_POST['sucursal'] ?? '');
                $fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '2026-08-27');
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 1);
                $costo_unit = (float)($_POST['costo_unit'] ?? 0);

                if (empty($proveedor_id) || empty($sucursal) || empty($producto) || $costo_unit <= 0) {
                    $error = 'Proveedor, Sucursal, Producto y Costo Unitario son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'referencia_pedido' => $referencia_pedido,
                        'pedido_compra_id' => $pedido_compra_id,
                        'proveedor_id' => $proveedor_id,
                        'sucursal' => $sucursal,
                        'fecha_vencimiento' => $fecha_vencimiento,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'costo_unit' => $costo_unit
                    ]);
                    if ($result) {
                        $success = 'Presupuesto de compra actualizado correctamente.';
                        $presupuestos = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar el presupuesto.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Presupuesto de compra eliminado correctamente.';
                    $presupuestos = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar el presupuesto.';
                }
            }
        }

        require_once __DIR__ . '/../views/presupuestos_compra/index.php';
    }
}
