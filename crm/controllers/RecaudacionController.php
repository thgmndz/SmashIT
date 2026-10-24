<?php
require_once __DIR__ . '/../models/RecaudacionModel.php';
require_once __DIR__ . '/../models/CashRegisterModel.php';

class RecaudacionController {
    private RecaudacionModel $model;
    private CashRegisterModel $cashRegisterModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new RecaudacionModel();
        $this->cashRegisterModel = new CashRegisterModel();
    }

    public function index(): void {
        $recaudaciones = $this->model->getAll();
        $cajas = $this->cashRegisterModel->getAll();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $cash_register_id = (int)($_POST['cash_register_id'] ?? 0);
                $monto_depositar = (float)($_POST['monto_depositar'] ?? 0);
                $banco = trim($_POST['banco'] ?? '');
                $nro_boleta = trim($_POST['nro_boleta'] ?? '');
                $observacion = trim($_POST['observacion'] ?? '');
                $estado = trim($_POST['estado'] ?? 'pendiente');

                if ($cash_register_id <= 0 || $monto_depositar <= 0 || empty($banco) || empty($nro_boleta)) {
                    $error = 'Caja, Monto, Banco y Nro. Boleta son obligatorios.';
                } else {
                    $result = $this->model->create([
                        'cash_register_id' => $cash_register_id,
                        'monto_depositar' => $monto_depositar,
                        'banco' => $banco,
                        'nro_boleta' => $nro_boleta,
                        'observacion' => $observacion,
                        'estado' => $estado
                    ]);
                    if ($result) {
                        $success = 'Recaudación registrada correctamente.';
                        $recaudaciones = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar la recaudación.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $cash_register_id = (int)($_POST['cash_register_id'] ?? 0);
                $monto_depositar = (float)($_POST['monto_depositar'] ?? 0);
                $banco = trim($_POST['banco'] ?? '');
                $nro_boleta = trim($_POST['nro_boleta'] ?? '');
                $observacion = trim($_POST['observacion'] ?? '');
                $estado = trim($_POST['estado'] ?? 'pendiente');

                if ($cash_register_id <= 0 || $monto_depositar <= 0 || empty($banco) || empty($nro_boleta)) {
                    $error = 'Caja, Monto, Banco y Nro. Boleta son obligatorios.';
                } else {
                    $result = $this->model->update($id, [
                        'cash_register_id' => $cash_register_id,
                        'monto_depositar' => $monto_depositar,
                        'banco' => $banco,
                        'nro_boleta' => $nro_boleta,
                        'observacion' => $observacion,
                        'estado' => $estado
                    ]);
                    if ($result) {
                        $success = 'Recaudación actualizada correctamente.';
                        $recaudaciones = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar la recaudación.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Recaudación eliminada correctamente.';
                    $recaudaciones = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar la recaudación.';
                }
            }
        }

        require_once __DIR__ . '/../views/recaudaciones/index.php';
    }
}
