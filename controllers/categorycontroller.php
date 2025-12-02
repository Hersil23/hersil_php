<?php
/**
 * CategoryController
 * Maneja categorías: listar, CRUD (admin)
 */

session_start();
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../config/config.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    // Listar todas las categorías
    public function listCategories() {
        return $this->categoryModel->getAll();
    }

    // Obtener categorías activas (con productos)
    public function getActiveCategories() {
        return $this->categoryModel->getActiveCategories();
    }

    // Obtener categoría por ID
    public function getCategoryById($id) {
        return $this->categoryModel->getById($id);
    }

    // ============================================
    // FUNCIONES DE ADMINISTRACIÓN (Solo Admin)
    // ============================================

    // Crear nueva categoría (Admin)
    public function createCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            // Obtener y sanitizar datos
            $nombre = sanitize($_POST['nombre'] ?? '');

            // Validaciones
            if (empty($nombre)) {
                $_SESSION['error'] = "El nombre de la categoría es requerido";
                redirect('/admin/categorias?action=create');
                return;
            }

            // Verificar si el nombre ya existe
            $this->categoryModel->nombre = $nombre;
            if ($this->categoryModel->nameExists()) {
                $_SESSION['error'] = "Ya existe una categoría con este nombre";
                redirect('/admin/categorias?action=create');
                return;
            }

            // Crear categoría
            $this->categoryModel->id_usuario = $_SESSION['user_id'];
            $this->categoryModel->nombre = $nombre;

            if ($this->categoryModel->create()) {
                $_SESSION['success'] = "Categoría creada exitosamente";
                redirect('/admin/categorias');
            } else {
                $_SESSION['error'] = "Error al crear la categoría";
                redirect('/admin/categorias?action=create');
            }
        }
    }

    // Actualizar categoría (Admin)
    public function updateCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            $id = intval($_POST['id'] ?? 0);
            $nombre = sanitize($_POST['nombre'] ?? '');

            // Validaciones
            if (empty($nombre)) {
                $_SESSION['error'] = "El nombre de la categoría es requerido";
                redirect('/admin/categorias?action=edit&id=' . $id);
                return;
            }

            // Verificar si el nombre ya existe (excluyendo la categoría actual)
            $this->categoryModel->id = $id;
            $this->categoryModel->nombre = $nombre;
            if ($this->categoryModel->nameExists()) {
                $_SESSION['error'] = "Ya existe una categoría con este nombre";
                redirect('/admin/categorias?action=edit&id=' . $id);
                return;
            }

            // Actualizar categoría
            if ($this->categoryModel->update()) {
                $_SESSION['success'] = "Categoría actualizada correctamente";
                redirect('/admin/categorias');
            } else {
                $_SESSION['error'] = "Error al actualizar la categoría";
                redirect('/admin/categorias?action=edit&id=' . $id);
            }
        }
    }

    // Eliminar categoría (Admin)
    public function deleteCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            $id = intval($_POST['id'] ?? 0);

            // Eliminar (soft delete)
            $this->categoryModel->id = $id;
            
            if ($this->categoryModel->delete($_SESSION['user_id'])) {
                $_SESSION['success'] = "Categoría eliminada correctamente";
            } else {
                $_SESSION['error'] = "No se puede eliminar la categoría porque tiene productos asociados";
            }

            redirect('/admin/categorias');
        }
    }
}

// Procesar acciones según la petición
if (isset($_GET['action'])) {
    $controller = new CategoryController();
    
    switch ($_GET['action']) {
        case 'admin-create':
            $controller->createCategory();
            break;
        case 'admin-update':
            $controller->updateCategory();
            break;
        case 'admin-delete':
            $controller->deleteCategory();
            break;
        default:
            redirect('/');
            break;
    }
}
?>