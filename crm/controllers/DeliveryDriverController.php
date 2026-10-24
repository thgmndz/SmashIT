<?php
require_once __DIR__ . '/../models/DeliveryDriverModel.php';

class DeliveryDriverController {
    private DeliveryDriverModel $model;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new DeliveryDriverModel();
    }

    public function index(): void {
        $drivers = $this->model->getAll();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $nombre = trim($_POST['nombre'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $vehiculo = trim($_POST['vehiculo'] ?? '');
                $chapa = trim($_POST['chapa'] ?? '');
                $activo = isset($_POST['activo']) ? 1 : 0;

                if (empty($nombre) || empty($telefono)) {
                    $error = 'Nombre y Teléfono son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'nombre' => $nombre,
                        'telefono' => $telefono,
                        'vehiculo' => $vehiculo,
                        'chapa' => $chapa,
                        'activo' => $activo
                    ]);
                    if ($result) {
                        $success = 'Chofer registrado correctamente.';
                        $drivers = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar el chofer.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $nombre = trim($_POST['nombre'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $vehiculo = trim($_POST['vehiculo'] ?? '');
                $chapa = trim($_POST['chapa'] ?? '');
                $activo = isset($_POST['activo']) ? 1 : 0;

                if (empty($nombre) || empty($telefono)) {
                    $error = 'Nombre y Teléfono son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'nombre' => $nombre,
                        'telefono' => $telefono,
                        'vehiculo' => $vehiculo,
                        'chapa' => $chapa,
                        'activo' => $activo
                    ]);
                    if ($result) {
                        $success = 'Datos del chofer actualizados.';
                        $drivers = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar chofer.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Chofer eliminado correctamente.';
                    $drivers = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar el chofer.';
                }
            }
        }

        require_once __DIR__ . '/../views/delivery_drivers/index.php';
    }
}
