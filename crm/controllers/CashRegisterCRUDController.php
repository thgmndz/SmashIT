<?php
require_once __DIR__ . '/../models/CashRegisterModel.php';

class CashRegisterCRUDController {
    private CashRegisterModel $model;

    public function __construct() {
        AuthController::checkAuth();
        $this->model = new CashRegisterModel();
    }

    public function index(): void {
        $cajas = $this->model->getAll();
        
        $db = CRMDatabase::getConnection();
        $funcionarios = $db->query("SELECT id, name FROM employees ORDER BY name ASC")->fetchAll();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $employee_id = (int)($_POST['employee_id'] ?? 0);
                $opening_amount = (float)($_POST['opening_amount'] ?? 0);

                if ($employee_id <= 0 || $opening_amount < 0) {
                    $error = 'Funcionario y Monto de Apertura son obligatorios.';
                } else {
                    // Check if there is already an open register
                    $openReg = $this->model->getOpenRegister();
                    if ($openReg) {
                        $error = 'Ya existe una caja abierta en el sistema. Debes cerrarla antes.';
                    } else {
                        $result = $this->model->openRegister($employee_id, $opening_amount);
                        if ($result) {
                            $success = 'Caja abierta correctamente.';
                            $cajas = $this->model->getAll();
                        } else {
                            $error = 'Error al abrir la caja.';
                        }
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $employee_id = (int)($_POST['employee_id'] ?? 0);
                $opening_amount = (float)($_POST['opening_amount'] ?? 0);
                $closing_amount = $_POST['closing_amount'] !== '' ? (float)$_POST['closing_amount'] : '';
                $calculated_amount = $_POST['calculated_amount'] !== '' ? (float)$_POST['calculated_amount'] : '';
                $difference = $_POST['difference'] !== '' ? (float)$_POST['difference'] : '';
                $status = trim($_POST['status'] ?? 'open');
                $opened_at = trim($_POST['opened_at'] ?? '');
                $closed_at = trim($_POST['closed_at'] ?? '');

                if ($employee_id <= 0 || empty($opened_at)) {
                    $error = 'Funcionario y Fecha de Apertura son obligatorios.';
                } else {
                    $result = $this->model->updateRegister($id, [
                        'employee_id' => $employee_id,
                        'opening_amount' => $opening_amount,
                        'closing_amount' => $closing_amount,
                        'calculated_amount' => $calculated_amount,
                        'difference' => $difference,
                        'status' => $status,
                        'opened_at' => $opened_at,
                        'closed_at' => $closed_at
                    ]);
                    if ($result) {
                        $success = 'Caja actualizada correctamente.';
                        $cajas = $this->model->getAll();
                    } else {
                        $error = 'Error al actualizar la caja.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->model->deleteRegister($id)) {
                    $success = 'Registro de caja eliminado correctamente.';
                    $cajas = $this->model->getAll();
                } else {
                    $error = 'Error al eliminar el registro de caja.';
                }
            }
        }

        require_once __DIR__ . '/../views/cash_registers/index.php';
    }
}
