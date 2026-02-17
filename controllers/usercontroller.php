<?php
/**
 * UserController
 * Maneja el perfil del usuario y administración de usuarios (admin)
 */

session_start();
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../config/config.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Actualizar perfil del usuario logueado
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que el usuario esté logueado
            if (!isLoggedIn()) {
                redirect('/login');
                return;
            }

            // Obtener y sanitizar datos
            $nombre = sanitize($_POST['nombre'] ?? '');
            $apellido = sanitize($_POST['apellido'] ?? '');

            // Validaciones
            $errors = [];

            if (empty($nombre)) {
                $errors[] = "El nombre es requerido";
            }

            if (empty($apellido)) {
                $errors[] = "El apellido es requerido";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                redirect('/perfil');
                return;
            }

            // Actualizar datos
            $this->userModel->id = $_SESSION['user_id'];
            $this->userModel->nombre = $nombre;
            $this->userModel->apellido = $apellido;

            if ($this->userModel->update()) {
                // Actualizar sesión
                $_SESSION['user_nombre'] = $nombre;
                $_SESSION['user_apellido'] = $apellido;

                $_SESSION['success'] = "Perfil actualizado correctamente";
                redirect('/perfil');
            } else {
                $_SESSION['error'] = "Error al actualizar el perfil";
                redirect('/perfil');
            }
        }
    }

    // Eliminar cuenta del usuario logueado
    public function deleteOwnAccount() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que el usuario esté logueado
            if (!isLoggedIn()) {
                redirect('/login');
                return;
            }

            // Verificar confirmación
            $confirmar = $_POST['confirmar_eliminar'] ?? '';
            
            if ($confirmar !== 'ELIMINAR') {
                $_SESSION['error'] = "Debes escribir 'ELIMINAR' para confirmar";
                redirect('/perfil');
                return;
            }

            // Eliminar cuenta (soft delete)
            $this->userModel->id = $_SESSION['user_id'];
            
            if ($this->userModel->delete($_SESSION['user_id'])) {
                // Destruir sesión y redirigir
                session_destroy();
                redirect('/login?deleted=1');
            } else {
                $_SESSION['error'] = "Error al eliminar la cuenta";
                redirect('/perfil');
            }
        }
    }

    // ============================================
    // FUNCIONES DE ADMINISTRACIÓN (Solo Admin)
    // ============================================

    // Listar todos los usuarios (Admin)
    public function listUsers() {
        // Verificar que sea administrador
        if (!isAdmin()) {
            redirect('/');
            return;
        }

        $users = $this->userModel->getAll();
        return $users;
    }

    // Obtener usuario por ID (Admin)
    public function getUserById($id) {
        if (!isAdmin()) {
            redirect('/');
            return;
        }

        return $this->userModel->getById($id);
    }

    // Crear nuevo usuario (Admin)
    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            // Obtener y sanitizar datos
            $nombre = sanitize($_POST['nombre'] ?? '');
            $apellido = sanitize($_POST['apellido'] ?? '');
            $correo = sanitize($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $rol = sanitize($_POST['rol'] ?? 'usuario');

            // Validaciones
            $errors = [];

            if (empty($nombre)) {
                $errors[] = "El nombre es requerido";
            }

            if (empty($apellido)) {
                $errors[] = "El apellido es requerido";
            }

            if (empty($correo) || !isValidEmail($correo)) {
                $errors[] = "El correo electrónico no es válido";
            }

            if (strlen($contrasena) < 8) {
                $errors[] = "La contraseña debe tener al menos 8 caracteres";
            }

            if (!in_array($rol, ['administrador', 'usuario'])) {
                $errors[] = "Rol inválido";
            }

            // Verificar si el correo ya existe
            $this->userModel->correo = $correo;
            if ($this->userModel->emailExists()) {
                $errors[] = "Este correo ya está registrado";
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                redirect('/admin/usuarios?action=create');
                return;
            }

            // Crear usuario
            $this->userModel->nombre = $nombre;
            $this->userModel->apellido = $apellido;
            $this->userModel->correo = $correo;
            $this->userModel->contrasena = $contrasena;

            if ($this->userModel->register()) {
                // Si el rol es administrador, actualizar
                if ($rol === 'administrador') {
                    $query = "UPDATE usuarios SET rol = 'administrador' WHERE correo = :correo";
                    $stmt = $this->userModel->conn->prepare($query);
                    $stmt->bindParam(':correo', $correo);
                    $stmt->execute();
                }

                $_SESSION['success'] = "Usuario creado exitosamente";
                redirect('/admin/usuarios');
            } else {
                $_SESSION['error'] = "Error al crear el usuario";
                redirect('/admin/usuarios?action=create');
            }
        }
    }

    // Actualizar usuario (Admin)
    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            $id = intval($_POST['id'] ?? 0);
            $nombre = sanitize($_POST['nombre'] ?? '');
            $apellido = sanitize($_POST['apellido'] ?? '');

            // Validaciones
            if (empty($nombre) || empty($apellido)) {
                $_SESSION['error'] = "Todos los campos son requeridos";
                redirect('/admin/usuarios?action=edit&id=' . $id);
                return;
            }

            // Actualizar
            $this->userModel->id = $id;
            $this->userModel->nombre = $nombre;
            $this->userModel->apellido = $apellido;

            if ($this->userModel->update()) {
                $_SESSION['success'] = "Usuario actualizado correctamente";
                redirect('/admin/usuarios');
            } else {
                $_SESSION['error'] = "Error al actualizar el usuario";
                redirect('/admin/usuarios?action=edit&id=' . $id);
            }
        }
    }

    // Eliminar usuario (Admin)
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar que sea administrador
            if (!isAdmin()) {
                redirect('/');
                return;
            }

            $id = intval($_POST['id'] ?? 0);

            // No permitir que se elimine a sí mismo
            if ($id === $_SESSION['user_id']) {
                $_SESSION['error'] = "No puedes eliminar tu propia cuenta desde aquí";
                redirect('/admin/usuarios');
                return;
            }

            // Eliminar (soft delete)
            $this->userModel->id = $id;
            
            if ($this->userModel->delete($_SESSION['user_id'])) {
                $_SESSION['success'] = "Usuario eliminado correctamente";
            } else {
                $_SESSION['error'] = "Error al eliminar el usuario";
            }

            redirect('/admin/usuarios');
        }
    }
}

// Procesar peticiones AJAX (JSON) para API
if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    // Check if this is an AJAX/API request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $isApi = strpos($_SERVER['REQUEST_URI'], '/api/users') !== false;

    if ($isAjax || $isApi) {
        $controller = new UserController();
        $response = ['success' => false, 'message' => 'Acción no válida'];

        switch ($action) {
            case 'delete':
                if (!isAdmin()) {
                    $response = ['success' => false, 'message' => 'No autorizado'];
                    break;
                }
                $id = intval($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $response = ['success' => false, 'message' => 'ID inválido'];
                    break;
                }
                if ($id === ($_SESSION['user_id'] ?? 0)) {
                    $response = ['success' => false, 'message' => 'No puedes eliminar tu propia cuenta desde aquí'];
                    break;
                }
                require_once __DIR__ . '/../models/user.php';
                $userModel = new User();
                $userModel->id = $id;
                if ($userModel->delete($_SESSION['user_id'])) {
                    $response = ['success' => true, 'message' => 'Usuario eliminado correctamente'];
                } else {
                    $response = ['success' => false, 'message' => 'Error al eliminar el usuario'];
                }
                break;
            case 'get':
                if (!isAdmin()) {
                    $response = ['success' => false, 'message' => 'No autorizado'];
                    break;
                }
                $id = intval($_GET['id'] ?? 0);
                $user = $controller->getUserById($id);
                if ($user) {
                    $response = ['success' => true, 'data' => $user];
                } else {
                    $response = ['success' => false, 'message' => 'Usuario no encontrado'];
                }
                break;
            case 'list':
                if (!isAdmin()) {
                    $response = ['success' => false, 'message' => 'No autorizado'];
                    break;
                }
                $users = $controller->listUsers();
                $response = ['success' => true, 'data' => $users];
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Non-AJAX form submissions (existing behavior)
    $controller = new UserController();

    switch ($action) {
        case 'update-profile':
            $controller->updateProfile();
            break;
        case 'delete-account':
            $controller->deleteOwnAccount();
            break;
        case 'admin-create':
            $controller->createUser();
            break;
        case 'admin-update':
            $controller->updateUser();
            break;
        case 'admin-delete':
            $controller->deleteUser();
            break;
        default:
            redirect('/');
            break;
    }
}
?>