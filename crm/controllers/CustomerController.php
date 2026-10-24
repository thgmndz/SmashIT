<?php
require_once __DIR__ . '/../models/CustomerModel.php';

class CustomerController {
    private CustomerModel $customerModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->customerModel = new CustomerModel();
    }

    public function index(): void {
        $customers = $this->customerModel->getAll();
        
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create') {
                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $ruc_ci = trim($_POST['ruc_ci'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $address = trim($_POST['address'] ?? '');
                
                if (empty($name) || empty($phone)) {
                    $error = 'Nombre y Teléfono son campos requeridos.';
                } else {
                    $result = $this->customerModel->create([
                        'name' => $name,
                        'phone' => $phone,
                        'ruc_ci' => $ruc_ci,
                        'email' => $email,
                        'address' => $address,
                        'total_orders' => 0
                    ]);
                    if ($result) {
                        $success = 'Cliente registrado correctamente.';
                        $customers = $this->customerModel->getAll();
                    } else {
                        $error = 'Error al registrar cliente. El teléfono ya podría estar registrado.';
                    }
                }
            } elseif ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $ruc_ci = trim($_POST['ruc_ci'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $total_orders = (int)($_POST['total_orders'] ?? 0);

                if (empty($name) || empty($phone)) {
                    $error = 'Nombre y Teléfono son requeridos.';
                } else {
                    $result = $this->customerModel->update($id, [
                        'name' => $name,
                        'phone' => $phone,
                        'ruc_ci' => $ruc_ci,
                        'email' => $email,
                        'address' => $address,
                        'total_orders' => $total_orders
                    ]);
                    if ($result) {
                        $success = 'Cliente actualizado correctamente.';
                        $customers = $this->customerModel->getAll();
                    } else {
                        $error = 'Error al actualizar el cliente.';
                    }
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($this->customerModel->delete($id)) {
                    $success = 'Cliente eliminado correctamente.';
                    $customers = $this->customerModel->getAll();
                } else {
                    $error = 'Error al eliminar el cliente.';
                }
            }
        }

        require_once __DIR__ . '/../views/customers/index.php';
    }
}
