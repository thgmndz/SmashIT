<?php
require_once __DIR__ . '/../models/PedidoCompraModel.php';

class PedidoCompraController {
    private PedidoCompraModel $model;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new PedidoCompraModel();
    }

    public function index(): void {
        $pedidos = $this->model->getAll();
        
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $sucursal = trim($_POST['sucursal'] ?? '');
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 0);
                
                if (empty($sucursal) || empty($producto) || $cantidad <= 0) {
                    $error = 'Sucursal, Producto y Cantidad (mayor a 0) son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'sucursal' => $sucursal,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad
                    ]);
                    if ($result) {
                        $success = 'Pedido de compra registrado correctamente.';
                        $pedidos = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar el pedido de compra.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $sucursal = trim($_POST['sucursal'] ?? '');
                $observacion = trim($_POST['observacion'] ?? '');
                $producto = trim($_POST['producto'] ?? '');
                $cantidad = (int)($_POST['cantidad'] ?? 0);

                if (empty($sucursal) || empty($producto) || $cantidad <= 0) {
                    $error = 'Sucursal, Producto y Cantidad (mayor a 0) son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'sucursal' => $sucursal,
                        'observacion' => $observacion,
                        'producto' => $producto,
                        'cantidad' => $cantidad
                    ]);
                    if ($result) {
                        $success = 'Pedido de compra actualizado correctamente.';
                        $pedidos = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar el pedido de compra.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Pedido de compra eliminado correctamente.';
                    $pedidos = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar el pedido de compra.';
                }
            }
        }

        require_once __DIR__ . '/../views/pedidos_compra/index.php';
    }
}
