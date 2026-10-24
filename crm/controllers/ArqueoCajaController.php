<?php
require_once __DIR__ . '/../models/ArqueoCajaModel.php';
require_once __DIR__ . '/../models/CashRegisterModel.php';

class ArqueoCajaController {
    private ArqueoCajaModel $model;
    private CashRegisterModel $cashRegisterModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new ArqueoCajaModel();
        $this->cashRegisterModel = new CashRegisterModel();
    }

    public function index(): void {
        $arqueos = $this->model->getAll();
        $cajas = $this->cashRegisterModel->getAll();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $cash_register_id = (int)($_POST['cash_register_id'] ?? 0);
                $efectivo_declarado = (float)($_POST['efectivo_declarado'] ?? 0);
                $tarjeta_declarado = (float)($_POST['tarjeta_declarado'] ?? 0);
                $cheque_declarado = (float)($_POST['cheque_declarado'] ?? 0);
                $monto_sistema = (float)($_POST['monto_sistema'] ?? 0);
                $observacion = trim($_POST['observacion'] ?? '');

                $declarado_total = $efectivo_declarado + $tarjeta_declarado + $cheque_declarado;
                $diferencia = $declarado_total - $monto_sistema;

                if ($cash_register_id <= 0) {
                    $error = 'Debe seleccionar una Caja.';
                } else {
                    $result = $this->model->create([
                        'cash_register_id' => $cash_register_id,
                        'efectivo_declarado' => $efectivo_declarado,
                        'tarjeta_declarado' => $tarjeta_declarado,
                        'cheque_declarado' => $cheque_declarado,
                        'monto_sistema' => $monto_sistema,
                        'diferencia' => $diferencia,
                        'observacion' => $observacion
                    ]);
                    if ($result) {
                        $success = 'Arqueo de caja registrado correctamente.';
                        $arqueos = $this->model->getAll();
                    } else {
                        $error = 'Error al registrar el arqueo de caja.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $cash_register_id = (int)($_POST['cash_register_id'] ?? 0);
                $efectivo_declarado = (float)($_POST['efectivo_declarado'] ?? 0);
                $tarjeta_declarado = (float)($_POST['tarjeta_declarado'] ?? 0);
                $cheque_declarado = (float)($_POST['cheque_declarado'] ?? 0);
                $monto_sistema = (float)($_POST['monto_sistema'] ?? 0);
                $observacion = trim($_POST['observacion'] ?? '');

                $declarado_total = $efectivo_declarado + $tarjeta_declarado + $cheque_declarado;
                $diferencia = $declarado_total - $monto_sistema;

                if ($cash_register_id <= 0) {
                    $error = 'Debe seleccionar una Caja.';
                } else {
                    $result = $this->model->update($id, [
                        'cash_register_id' => $cash_register_id,
                        'efectivo_declarado' => $efectivo_declarado,
                        'tarjeta_declarado' => $tarjeta_declarado,
                        'cheque_declarado' => $cheque_declarado,
                        'monto_sistema' => $monto_sistema,
                        'diferencia' => $diferencia,
                        'observacion' => $observacion
                    ]);
                    if ($result) {
                        $success = 'Arqueo de caja actualizado correctamente.';
                        $arqueos = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar el arqueo de caja.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->delete($id)) {
                    $success = 'Arqueo de caja eliminado correctamente.';
                    $arqueos = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar el arqueo de caja.';
                }
            }
        }

        require_once __DIR__ . '/../views/arqueos/index.php';
    }
}
