<?php
/**
 * ProductController
 * Maneja productos: listar, buscar, filtrar, CRUD (admin)
 */

session_start();
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../config/config.php';

class ProductController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    // Listar todos los productos
    public function listProducts() {
        return $this->productModel->getAll();
    }

    // Filtrar productos por categoría
    public function filterByCategory($categoria_id) {
        return $this->productModel->getByCategory($categoria_id);
    }

    // Buscar productos
    public function searchProducts($keyword) {
        if (empty($keyword)) {
            return $this->productModel->getAll();
        }
        return $this->productModel->search($keyword);
    }

    // Obtener producto por ID
    public function getProductById($id) {
        return $this->productModel->getById($id);
    }

    // ============================================
    // FUNCIONES DE ADMINISTRACIÓN (Solo Admin)
    // ============================================

    // Crear nuevo producto (Admin)
    public function createProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            // Obtener y sanitizar datos
            $nombre = sanitize($_POST['nombre'] ?? '');
            $descripcion = sanitize($_POST['descripcion'] ?? '');
            $id_categoria = intval($_POST['id_categoria'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $precio = floatval($_POST['precio'] ?? 0);

            // Validaciones
            $errors = [];

            if (empty($nombre)) {
                $errors[] = "El nombre del producto es requerido";
            }

            if (empty($descripcion)) {
                $errors[] = "La descripción es requerida";
            }

            if ($id_categoria <= 0) {
                $errors[] = "Debes seleccionar una categoría válida";
            }

            if ($stock < 0) {
                $errors[] = "El stock no puede ser negativo";
            }

            if ($precio <= 0) {
                $errors[] = "El precio debe ser mayor a 0";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                redirect('/admin/productos?action=create');
                return;
            }

            // Crear producto
            $this->productModel->id_usuario = $_SESSION['user_id'];
            $this->productModel->id_categoria = $id_categoria;
            $this->productModel->nombre = $nombre;
            $this->productModel->descripcion = $descripcion;
            $this->productModel->stock = $stock;
            $this->productModel->precio = $precio;

            if ($this->productModel->create()) {
                $_SESSION['success'] = "Producto creado exitosamente";
                redirect('/admin/productos');
            } else {
                $_SESSION['error'] = "Error al crear el producto";
                redirect('/admin/productos?action=create');
            }
        }
    }

    // Actualizar producto (Admin)
    public function updateProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            $id = intval($_POST['id'] ?? 0);
            $nombre = sanitize($_POST['nombre'] ?? '');
            $descripcion = sanitize($_POST['descripcion'] ?? '');
            $id_categoria = intval($_POST['id_categoria'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $precio = floatval($_POST['precio'] ?? 0);

            // Validaciones
            $errors = [];

            if (empty($nombre)) {
                $errors[] = "El nombre del producto es requerido";
            }

            if (empty($descripcion)) {
                $errors[] = "La descripción es requerida";
            }

            if ($id_categoria <= 0) {
                $errors[] = "Debes seleccionar una categoría válida";
            }

            if ($stock < 0) {
                $errors[] = "El stock no puede ser negativo";
            }

            if ($precio <= 0) {
                $errors[] = "El precio debe ser mayor a 0";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                redirect('/admin/productos?action=edit&id=' . $id);
                return;
            }

            // Actualizar producto
            $this->productModel->id = $id;
            $this->productModel->id_categoria = $id_categoria;
            $this->productModel->nombre = $nombre;
            $this->productModel->descripcion = $descripcion;
            $this->productModel->stock = $stock;
            $this->productModel->precio = $precio;

            if ($this->productModel->update()) {
                $_SESSION['success'] = "Producto actualizado correctamente";
                redirect('/admin/productos');
            } else {
                $_SESSION['error'] = "Error al actualizar el producto";
                redirect('/admin/productos?action=edit&id=' . $id);
            }
        }
    }

    // Eliminar producto (Admin)
    public function deleteProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            $id = intval($_POST['id'] ?? 0);

            // Eliminar (soft delete)
            $this->productModel->id = $id;
            
            if ($this->productModel->delete($_SESSION['user_id'])) {
                $_SESSION['success'] = "Producto eliminado correctamente";
            } else {
                $_SESSION['error'] = "Error al eliminar el producto";
            }

            redirect('/admin/productos');
        }
    }

    // Obtener productos con stock bajo (Admin)
    public function getLowStockProducts() {
        if (!isAdmin()) {
            redirect('/');
            return;
        }

        return $this->productModel->getLowStock();
    }

    // Obtener estadísticas de productos por categoría (Admin)
    public function getProductStats() {
        if (!isAdmin()) {
            redirect('/');
            return;
        }

        return $this->productModel->countByCategory();
    }
}

// Procesar acciones según la petición
if (isset($_GET['action'])) {
    $controller = new ProductController();
    
    switch ($_GET['action']) {
        case 'admin-create':
            $controller->createProduct();
            break;
        case 'admin-update':
            $controller->updateProduct();
            break;
        case 'admin-delete':
            $controller->deleteProduct();
            break;
        default:
            redirect('/');
            break;
    }
}
?>