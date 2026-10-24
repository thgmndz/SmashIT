<?php
require_once __DIR__ . '/../models/ProveedorModel.php';

class ProveedorController {
    private ProveedorModel $model;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new ProveedorModel();
    }

    public function index(): void {
        $proveedores = $this->model->getAll();
        
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $nombre = trim($_POST['nombre'] ?? '');
                $ruc = trim($_POST['ruc'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $categoria_producto = trim($_POST['categoria_producto'] ?? '');
                
                if (empty($nombre) || empty($ruc) || empty($telefono) || empty($categoria_producto)) {
                    $error = 'Todos los campos son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'nombre' => $nombre,
                        'ruc' => $ruc,
                        'telefono' => $telefono,
                        'categoria_producto' => $categoria_producto
                    ]);
                    if ($result) {
                        $success = 'Proveedor registrado correctamente.';
                        $proveedores = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar el proveedor.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $nombre = trim($_POST['nombre'] ?? '');
                $ruc = trim($_POST['ruc'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $categoria_producto = trim($_POST['categoria_producto'] ?? '');

                if (empty($nombre) || empty($ruc) || empty($telefono) || empty($categoria_producto)) {
                    $error = 'Todos los campos son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'nombre' => $nombre,
                        'ruc' => $ruc,
                        'telefono' => $telefono,
                        'categoria_producto' => $categoria_producto
                    ]);
                    if ($result) {
                        $success = 'Proveedor actualizado correctamente.';
                        $proveedores = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar el proveedor.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Proveedor eliminado correctamente.';
                    $proveedores = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar el proveedor.';
                }
            }
        }

        require_once __DIR__ . '/../views/proveedores/index.php';
    }
}
