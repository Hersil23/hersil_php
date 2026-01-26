<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Controlador de Categorías
 * Maneja las operaciones CRUD de categorías
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../utils/Security.php';
require_once __DIR__ . '/../utils/BunnyStorage.php';
require_once __DIR__ . '/../utils/ImageCompressor.php';

class CategoryController {
    private $categoryModel;
    private $bunnyStorage;
    private $imageCompressor;

    public function __construct() {
        $this->categoryModel = new Category();
        $this->bunnyStorage = new BunnyStorage();
        $this->imageCompressor = new ImageCompressor(800, 800, 85);
    }

    /**
     * Crear nueva categoría
     */
    public function create() {
        // Verificar admin
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        // Validar CSRF
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        // Validar campos requeridos
        $nombre = Security::sanitize($_POST['nombre'] ?? '');
        $descripcion = Security::sanitize($_POST['descripcion'] ?? '');

        if (empty($nombre)) {
            return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        // Verificar nombre duplicado
        $this->categoryModel->nombre = $nombre;
        if ($this->categoryModel->nameExists()) {
            return ['success' => false, 'message' => 'Ya existe una categoría con este nombre'];
        }

        // Procesar imagen si se subió
        $imagen_url = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url = $uploadResult['url'];
        }

        // Crear categoría
        $this->categoryModel->id_usuario = $_SESSION['user_id'];
        $this->categoryModel->nombre = $nombre;
        $this->categoryModel->descripcion = $descripcion;
        $this->categoryModel->imagen_url = $imagen_url;
        $this->categoryModel->activo = 1;

        if ($this->categoryModel->create()) {
            Security::logAdminAction('create_category', ['id' => $this->categoryModel->id, 'nombre' => $nombre]);
            return ['success' => true, 'message' => 'Categoría creada correctamente', 'id' => $this->categoryModel->id];
        }

        return ['success' => false, 'message' => 'Error al crear la categoría'];
    }

    /**
     * Actualizar categoría
     */
    public function update() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        $id = intval($_POST['id'] ?? 0);
        $nombre = Security::sanitize($_POST['nombre'] ?? '');
        $descripcion = Security::sanitize($_POST['descripcion'] ?? '');

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        if (empty($nombre)) {
            return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        // Verificar que existe
        $categoria = $this->categoryModel->getById($id);
        if (!$categoria) {
            return ['success' => false, 'message' => 'Categoría no encontrada'];
        }

        // Verificar nombre duplicado
        $this->categoryModel->nombre = $nombre;
        if ($this->categoryModel->nameExists($id)) {
            return ['success' => false, 'message' => 'Ya existe otra categoría con este nombre'];
        }

        // Procesar nueva imagen si se subió
        $imagen_url = $categoria['imagen_url'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            // Eliminar imagen anterior si existe
            if (!empty($categoria['imagen_url'])) {
                $this->bunnyStorage->delete($categoria['imagen_url']);
            }
            
            $uploadResult = $this->uploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url = $uploadResult['url'];
        }

        // Actualizar
        $this->categoryModel->id = $id;
        $this->categoryModel->nombre = $nombre;
        $this->categoryModel->descripcion = $descripcion;
        $this->categoryModel->imagen_url = $imagen_url;

        if ($this->categoryModel->update()) {
            Security::logAdminAction('update_category', ['id' => $id, 'nombre' => $nombre]);
            return ['success' => true, 'message' => 'Categoría actualizada correctamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar la categoría'];
    }

    /**
     * Eliminar categoría (soft delete)
     */
    public function delete() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        // Verificar que existe
        $categoria = $this->categoryModel->getById($id);
        if (!$categoria) {
            return ['success' => false, 'message' => 'Categoría no encontrada'];
        }

        $this->categoryModel->id = $id;

        if ($this->categoryModel->delete($_SESSION['user_id'])) {
            Security::logAdminAction('delete_category', ['id' => $id, 'nombre' => $categoria['nombre']]);
            return ['success' => true, 'message' => 'Categoría eliminada correctamente'];
        }

        return ['success' => false, 'message' => 'No se puede eliminar. La categoría tiene productos asociados.'];
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActive() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        if ($this->categoryModel->toggleActive($id)) {
            $categoria = $this->categoryModel->getById($id);
            $estado = $categoria['activo'] ? 'activada' : 'desactivada';
            Security::logAdminAction('toggle_category', ['id' => $id, 'estado' => $estado]);
            return ['success' => true, 'message' => "Categoría {$estado} correctamente", 'activo' => $categoria['activo']];
        }

        return ['success' => false, 'message' => 'Error al cambiar el estado'];
    }

    /**
     * Obtener categoría por ID (para editar)
     */
    public function get() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        $categoria = $this->categoryModel->getById($id);

        if ($categoria) {
            return ['success' => true, 'data' => $categoria];
        }

        return ['success' => false, 'message' => 'Categoría no encontrada'];
    }

    /**
     * Eliminar solo la imagen
     */
    public function deleteImage() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        $categoria = $this->categoryModel->getById($id);
        if (!$categoria) {
            return ['success' => false, 'message' => 'Categoría no encontrada'];
        }

        // Eliminar de Bunny
        if (!empty($categoria['imagen_url'])) {
            $this->bunnyStorage->delete($categoria['imagen_url']);
        }

        // Actualizar BD
        if ($this->categoryModel->updateImage($id, null)) {
            return ['success' => true, 'message' => 'Imagen eliminada correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar la imagen'];
    }

    /**
     * Subir y comprimir imagen
     */
    private function uploadImage($file) {
        // Validar archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $validation = Security::validateUpload($file, $allowedTypes, 5 * 1024 * 1024);

        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }

        // Comprimir imagen
        $compressed = $this->imageCompressor->compressFromUpload($file);

        if (!$compressed['success']) {
            return ['success' => false, 'message' => $compressed['message']];
        }

        // Subir a Bunny
        $fileName = 'cat_' . uniqid() . '_' . time() . '.jpg';
        $uploadResult = $this->bunnyStorage->upload($compressed['path'], 'categories/' . $fileName);

        // Limpiar archivo temporal
        $this->imageCompressor->cleanup($compressed['path']);

        if ($uploadResult['success']) {
            return ['success' => true, 'url' => $uploadResult['url']];
        }

        return ['success' => false, 'message' => 'Error al subir la imagen'];
    }
}

// Procesar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new CategoryController();
    $action = $_REQUEST['action'] ?? '';

    $response = ['success' => false, 'message' => 'Acción no válida'];

    switch ($action) {
        case 'create':
            $response = $controller->create();
            break;
        case 'update':
            $response = $controller->update();
            break;
        case 'delete':
            $response = $controller->delete();
            break;
        case 'toggle':
            $response = $controller->toggleActive();
            break;
        case 'get':
            $response = $controller->get();
            break;
        case 'delete_image':
            $response = $controller->deleteImage();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>