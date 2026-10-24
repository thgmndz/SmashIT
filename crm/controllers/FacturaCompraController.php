<?php
require_once __DIR__ . '/../models/FacturaCompraModel.php';
require_once __DIR__ . '/../models/OrdenCompraModel.php';
require_once __DIR__ . '/../models/ProveedorModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class FacturaCompraController {
    private FacturaCompraModel $model;
    private OrdenCompraModel $ordenModel;
    private ProveedorModel $proveedorModel;
    private ProductModel $productModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new FacturaCompraModel();
        $this->ordenModel = new OrdenCompraModel();
        $this->proveedorModel = new ProveedorModel();
        $this->productModel = new ProductModel();
    }

    public function index(): void {
        $facturas = $this->model->getAll();
        $ordenes = $this->ordenModel->getAll();
        $proveedores = $this->proveedorModel->getAll();
        $products = $this->productModel->getAll();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $referencia_orden_compra = trim($_POST['referencia_orden_compra'] ?? '');
                $orden_compra_id = $_POST['orden_compra_id'] !== '' ? (int)$_POST['orden_compra_id'] : null;
                $fecha = trim($_POST['fecha'] ?? '2026-08-12');
                $condicion = trim($_POST['condicion'] ?? 'CONTADO');
                $numero_factura = trim($_POST['numero_factura'] ?? '001-001-0000001');
                $nro_timbrado = trim($_POST['nro_timbrado'] ?? '');
                $vencimiento_timbrado = trim($_POST['vencimiento_timbrado'] ?? '2026-08-12');
                $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
                $sucursal = trim($_POST['sucursal'] ?? '');
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 1);
                $costo_unit = (float)($_POST['costo_unit'] ?? 0);

                if (empty($numero_factura) || empty($nro_timbrado) || empty($proveedor_id) || empty($sucursal) || empty($producto) || $costo_unit <= 0) {
                    $error = 'Número Factura, Timbrado, Proveedor, Sucursal, Producto y Costo Unitario son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'referencia_orden_compra' => $referencia_orden_compra,
                        'orden_compra_id' => $orden_compra_id,
                        'fecha' => $fecha,
                        'condicion' => $condicion,
                        'numero_factura' => $numero_factura,
                        'nro_timbrado' => $nro_timbrado,
                        'vencimiento_timbrado' => $vencimiento_timbrado,
                        'proveedor_id' => $proveedor_id,
                        'sucursal' => $sucursal,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'costo_unit' => $costo_unit
                    ]);
                    if ($result) {
                        $success = 'Factura de compra registrada correctamente.';
                        $facturas = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar la factura de compra.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $referencia_orden_compra = trim($_POST['referencia_orden_compra'] ?? '');
                $orden_compra_id = $_POST['orden_compra_id'] !== '' ? (int)$_POST['orden_compra_id'] : null;
                $fecha = trim($_POST['fecha'] ?? '2026-08-12');
                $condicion = trim($_POST['condicion'] ?? 'CONTADO');
                $numero_factura = trim($_POST['numero_factura'] ?? '001-001-0000001');
                $nro_timbrado = trim($_POST['nro_timbrado'] ?? '');
                $vencimiento_timbrado = trim($_POST['vencimiento_timbrado'] ?? '2026-08-12');
                $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
                $sucursal = trim($_POST['sucursal'] ?? '');
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 1);
                $costo_unit = (float)($_POST['costo_unit'] ?? 0);

                if (empty($numero_factura) || empty($nro_timbrado) || empty($proveedor_id) || empty($sucursal) || empty($producto) || $costo_unit <= 0) {
                    $error = 'Número Factura, Timbrado, Proveedor, Sucursal, Producto y Costo Unitario son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'referencia_orden_compra' => $referencia_orden_compra,
                        'orden_compra_id' => $orden_compra_id,
                        'fecha' => $fecha,
                        'condicion' => $condicion,
                        'numero_factura' => $numero_factura,
                        'nro_timbrado' => $nro_timbrado,
                        'vencimiento_timbrado' => $vencimiento_timbrado,
                        'proveedor_id' => $proveedor_id,
                        'sucursal' => $sucursal,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad,
                        'costo_unit' => $costo_unit
                    ]);
                    if ($result) {
                        $success = 'Factura de compra actualizada correctamente.';
                        $facturas = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar la factura de compra.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Factura de compra eliminada correctamente.';
                    $facturas = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar la factura de compra.';
                }
            }
        }

        require_once __DIR__ . '/../views/facturas_compra/index.php';
    }
}
