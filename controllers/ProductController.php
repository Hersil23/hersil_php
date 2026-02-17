<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Controlador de Productos
 * Maneja las operaciones CRUD de productos
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../utils/Security.php';
require_once __DIR__ . '/../utils/BunnyStorage.php';
require_once __DIR__ . '/../utils/ImageCompressor.php';

class ProductController {
    private $productModel;
    private $categoryModel;
    private $bunnyStorage;
    private $imageCompressor;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->bunnyStorage = new BunnyStorage();
        $this->imageCompressor = new ImageCompressor(1200, 1200, 85);
    }

    /**
     * Crear nuevo producto
     */
    public function create() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido'];
        }

        $nombre = Security::sanitize($_POST['nombre'] ?? '');
        $descripcion = Security::sanitize($_POST['descripcion'] ?? '');
        $id_categoria = intval($_POST['id_categoria'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $precio = floatval($_POST['precio'] ?? 0);

        if (empty($nombre)) {
            return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        if ($id_categoria <= 0) {
            return ['success' => false, 'message' => 'Selecciona una categoría válida'];
        }

        if ($precio <= 0) {
            return ['success' => false, 'message' => 'El precio debe ser mayor a 0'];
        }

        if ($stock < 0) {
            return ['success' => false, 'message' => 'El stock no puede ser negativo'];
        }

        // Procesar imagen principal
        $imagen_url = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url = $uploadResult['url'];
        }

        // Procesar imagen 2
        $imagen_url_2 = null;
        if (isset($_FILES['imagen_2']) && $_FILES['imagen_2']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($_FILES['imagen_2']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url_2 = $uploadResult['url'];
        }

        // Procesar imagen 3
        $imagen_url_3 = null;
        if (isset($_FILES['imagen_3']) && $_FILES['imagen_3']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadImage($_FILES['imagen_3']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url_3 = $uploadResult['url'];
        }

        // Crear producto
        $this->productModel->id_usuario = $_SESSION['user_id'];
        $this->productModel->id_categoria = $id_categoria;
        $this->productModel->nombre = $nombre;
        $this->productModel->descripcion = $descripcion;
        $this->productModel->imagen_url = $imagen_url;
        $this->productModel->imagen_url_2 = $imagen_url_2;
        $this->productModel->imagen_url_3 = $imagen_url_3;
        $this->productModel->stock = $stock;
        $this->productModel->precio = $precio;

        if ($this->productModel->create()) {
            Security::logAdminAction('create_product', ['id' => $this->productModel->id, 'nombre' => $nombre]);
            return ['success' => true, 'message' => 'Producto creado correctamente', 'id' => $this->productModel->id];
        }

        return ['success' => false, 'message' => 'Error al crear el producto'];
    }

    /**
     * Actualizar producto
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
        $id_categoria = intval($_POST['id_categoria'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $precio = floatval($_POST['precio'] ?? 0);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        if (empty($nombre)) {
            return ['success' => false, 'message' => 'El nombre es requerido'];
        }

        if ($id_categoria <= 0) {
            return ['success' => false, 'message' => 'Selecciona una categoría válida'];
        }

        if ($precio <= 0) {
            return ['success' => false, 'message' => 'El precio debe ser mayor a 0'];
        }

        if ($stock < 0) {
            return ['success' => false, 'message' => 'El stock no puede ser negativo'];
        }

        // Verificar que existe
        $producto = $this->productModel->getById($id);
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        // Procesar imagen principal
        $imagen_url = $producto['imagen_url'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            if (!empty($producto['imagen_url'])) {
                $this->bunnyStorage->delete($producto['imagen_url']);
            }
            $uploadResult = $this->uploadImage($_FILES['imagen']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url = $uploadResult['url'];
        }

        // Procesar imagen 2
        $imagen_url_2 = $producto['imagen_url_2'] ?? null;
        if (isset($_FILES['imagen_2']) && $_FILES['imagen_2']['error'] === UPLOAD_ERR_OK) {
            if (!empty($producto['imagen_url_2'])) {
                $this->bunnyStorage->delete($producto['imagen_url_2']);
            }
            $uploadResult = $this->uploadImage($_FILES['imagen_2']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url_2 = $uploadResult['url'];
        }

        // Procesar imagen 3
        $imagen_url_3 = $producto['imagen_url_3'] ?? null;
        if (isset($_FILES['imagen_3']) && $_FILES['imagen_3']['error'] === UPLOAD_ERR_OK) {
            if (!empty($producto['imagen_url_3'])) {
                $this->bunnyStorage->delete($producto['imagen_url_3']);
            }
            $uploadResult = $this->uploadImage($_FILES['imagen_3']);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $imagen_url_3 = $uploadResult['url'];
        }

        // Actualizar
        $this->productModel->id = $id;
        $this->productModel->id_categoria = $id_categoria;
        $this->productModel->nombre = $nombre;
        $this->productModel->descripcion = $descripcion;
        $this->productModel->imagen_url = $imagen_url;
        $this->productModel->imagen_url_2 = $imagen_url_2;
        $this->productModel->imagen_url_3 = $imagen_url_3;
        $this->productModel->stock = $stock;
        $this->productModel->precio = $precio;

        if ($this->productModel->update()) {
            Security::logAdminAction('update_product', ['id' => $id, 'nombre' => $nombre]);
            return ['success' => true, 'message' => 'Producto actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar el producto'];
    }

    /**
     * Eliminar producto (soft delete)
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

        $producto = $this->productModel->getById($id);
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        $this->productModel->id = $id;

        if ($this->productModel->delete($_SESSION['user_id'])) {
            Security::logAdminAction('delete_product', ['id' => $id, 'nombre' => $producto['nombre']]);
            return ['success' => true, 'message' => 'Producto eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el producto'];
    }

    /**
     * Obtener producto por ID (para editar)
     */
    public function get() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID inválido'];
        }

        $producto = $this->productModel->getById($id);

        if ($producto) {
            return ['success' => true, 'data' => $producto];
        }

        return ['success' => false, 'message' => 'Producto no encontrado'];
    }

    /**
     * Listar todos los productos
     */
    public function list() {
        if (!isAdmin()) {
            return ['success' => false, 'message' => 'No autorizado'];
        }

        $productos = $this->productModel->getAll();
        return ['success' => true, 'data' => $productos];
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

        $producto = $this->productModel->getById($id);
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        // Eliminar de Bunny
        if (!empty($producto['imagen_url'])) {
            $this->bunnyStorage->delete($producto['imagen_url']);
        }

        // Actualizar BD
        if ($this->productModel->updateImage($id, null)) {
            return ['success' => true, 'message' => 'Imagen eliminada correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar la imagen'];
    }

    /**
     * Eliminar imagen 2
     */
    public function deleteImage2() {
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

        $producto = $this->productModel->getById($id);
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        if (!empty($producto['imagen_url_2'])) {
            $this->bunnyStorage->delete($producto['imagen_url_2']);
        }

        if ($this->productModel->updateImage2($id, null)) {
            return ['success' => true, 'message' => 'Imagen 2 eliminada correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar la imagen'];
    }

    /**
     * Eliminar imagen 3
     */
    public function deleteImage3() {
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

        $producto = $this->productModel->getById($id);
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        if (!empty($producto['imagen_url_3'])) {
            $this->bunnyStorage->delete($producto['imagen_url_3']);
        }

        if ($this->productModel->updateImage3($id, null)) {
            return ['success' => true, 'message' => 'Imagen 3 eliminada correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar la imagen'];
    }

    /**
     * Subir y comprimir imagen
     */
    private function uploadImage($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $validation = Security::validateUpload($file, $allowedTypes, 5 * 1024 * 1024);

        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }

        $compressed = $this->imageCompressor->compressFromUpload($file);

        if (!$compressed['success']) {
            return ['success' => false, 'message' => $compressed['message']];
        }

        $fileName = 'prod_' . uniqid() . '_' . time() . '.jpg';
        $uploadResult = $this->bunnyStorage->upload($compressed['path'], 'products/' . $fileName);

        $this->imageCompressor->cleanup($compressed['path']);

        if ($uploadResult['success']) {
            return ['success' => true, 'url' => $uploadResult['url']];
        }

        return ['success' => false, 'message' => 'Error al subir la imagen'];
    }
}

// Procesar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new ProductController();
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
        case 'get':
            $response = $controller->get();
            break;
        case 'list':
            $response = $controller->list();
            break;
        case 'delete_image':
            $response = $controller->deleteImage();
            break;
        case 'delete_image_2':
            $response = $controller->deleteImage2();
            break;
        case 'delete_image_3':
            $response = $controller->deleteImage3();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>