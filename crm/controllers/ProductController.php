<?php
require_once __DIR__ . '/../models/ProductModel.php';

class ProductController {
    private ProductModel $productModel;

    public function __construct() {
        AuthController::checkAuth();
        $this->productModel = new ProductModel();
    }

    public function index(): void {
        $products = $this->productModel->getAll();
        $categories = $this->productModel->getCategories();
        
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'edit') {
                $id = (int)($_POST['id'] ?? 0);
                $price = (float)($_POST['base_price'] ?? 0.0);
                $badge = trim($_POST['badge'] ?? '');
                $isAvailable = isset($_POST['is_available']);

                $imageUrl = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['image']['tmp_name'];
                    $fileName = $_FILES['image']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = __DIR__ . '/../../assets/images/';
                        
                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0777, true);
                        }

                        $dest_path = $uploadFileDir . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $imageUrl = 'assets/images/' . $newFileName;
                        } else {
                            $error = 'Error al guardar el archivo de imagen.';
                        }
                    } else {
                        $error = 'Tipo de archivo no permitido (solo JPG, PNG, WEBP).';
                    }
                }

                if (empty($error)) {
                    if ($id > 0 && $price >= 0) {
                        $result = $this->productModel->updatePriceAvailabilityAndBadge($id, $price, $isAvailable, $badge, $imageUrl);
                        if ($result) {
                            $success = 'Producto actualizado correctamente.';
                            $products = $this->productModel->getAll();
                        } else {
                            $error = 'Error al actualizar el producto en la base de datos.';
                        }
                    } else {
                        $error = 'Precio inválido.';
                    }
                }
            } elseif ($action === 'create') {
                $category_id = (int)($_POST['category_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price = (float)($_POST['base_price'] ?? 0.0);
                $badge = trim($_POST['badge'] ?? '');
                $isAvailable = isset($_POST['is_available']);
                $isFeatured = isset($_POST['is_featured']);

                if ($category_id <= 0 || empty($name) || $price < 0) {
                    $error = 'Por favor, complete todos los campos obligatorios y con valores válidos.';
                } else {
                    $imageUrl = 'assets/images/classic-smash.jpg';
                    
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['image']['tmp_name'];
                        $fileName = $_FILES['image']['name'];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($fileExtension, $allowedExtensions)) {
                            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                            $uploadFileDir = __DIR__ . '/../../assets/images/';
                            
                            if (!is_dir($uploadFileDir)) {
                                mkdir($uploadFileDir, 0777, true);
                            }

                            $dest_path = $uploadFileDir . $newFileName;
                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $imageUrl = 'assets/images/' . $newFileName;
                            } else {
                                $error = 'Error al guardar la imagen del producto.';
                            }
                        } else {
                            $error = 'Tipo de archivo no permitido para la imagen (solo JPG, PNG, WEBP).';
                        }
                    }

                    if (empty($error)) {
                        $slug = ProductModel::slugify($name);
                        
                        $result = $this->productModel->insert([
                            'category_id' => $category_id,
                            'name' => $name,
                            'slug' => $slug,
                            'description' => $description,
                            'base_price' => $price,
                            'image_url' => $imageUrl,
                            'badge' => !empty($badge) ? $badge : null,
                            'is_available' => $isAvailable,
                            'is_featured' => $isFeatured
                        ]);

                        if ($result) {
                            $success = '¡Producto creado correctamente!';
                            $products = $this->productModel->getAll();
                        } else {
                            $error = 'Error al insertar el producto en la base de datos (posible nombre/slug duplicado).';
                        }
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/products/index.php';
    }
}
