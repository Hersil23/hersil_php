<?php
/**
 * AuthController
 * Maneja autenticación: login, registro, logout, recuperar contraseña
 */

session_start();
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../utils/mailer.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $userModel;
    private $mailer;

    public function __construct() {
        $this->userModel = new User();
        $this->mailer = new Mailer();
    }

    // Procesar registro
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Si ya está logueado, no puede registrarse
            if (isLoggedIn()) {
                redirect('/');
                return;
            }
            
            // Obtener y sanitizar datos
            $nombre = sanitize($_POST['nombre'] ?? '');
            $apellido = sanitize($_POST['apellido'] ?? '');
            $correo = sanitize($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

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

            if ($contrasena !== $confirmar_contrasena) {
                $errors[] = "Las contraseñas no coinciden";
            }

            // Verificar si el correo ya existe
            $this->userModel->correo = $correo;
            if ($this->userModel->emailExists()) {
                $errors[] = "Este correo ya está registrado";
            }

            // Si hay errores, regresar a la vista con los errores
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                redirect('/public/register');
                return;
            }

            // Crear usuario
            $this->userModel->nombre = $nombre;
            $this->userModel->apellido = $apellido;
            $this->userModel->correo = $correo;
            $this->userModel->contrasena = $contrasena;

            if ($this->userModel->register()) {
                // Enviar correo de bienvenida
                $this->mailer->sendWelcomeEmail($correo, $nombre);

                // Mensaje de éxito
                $_SESSION['success'] = "¡Registro exitoso! Por favor inicia sesión.";
                redirect('/public/login');
            } else {
                $_SESSION['error'] = "Error al registrar el usuario. Intenta de nuevo.";
                redirect('/public/register');
            }
        }
    }

    // Procesar login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Si ya está logueado, no puede hacer login de nuevo
            if (isLoggedIn()) {
                redirect('/');
                return;
            }
            
            // Obtener datos
            $correo = sanitize($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';

            // Validaciones
            if (empty($correo) || empty($contrasena)) {
                $_SESSION['error'] = "Todos los campos son requeridos";
                redirect('/public/login');
                return;
            }

            // Intentar login
            $this->userModel->correo = $correo;
            $this->userModel->contrasena = $contrasena;

            if ($this->userModel->login()) {
                // Crear sesión
                $_SESSION['user_id'] = $this->userModel->id;
                $_SESSION['user_nombre'] = $this->userModel->nombre;
                $_SESSION['user_apellido'] = $this->userModel->apellido;
                $_SESSION['user_correo'] = $this->userModel->correo;
                $_SESSION['user_rol'] = $this->userModel->rol;

                // Redirigir según el rol
                if ($this->userModel->rol === 'administrador') {
                    redirect('/public/admin/usuarios');
                } else {
                    redirect('/public/productos');
                }
            } else {
                $_SESSION['error'] = "Correo o contraseña incorrectos";
                redirect('/public/login');
            }
        }
    }

    // Logout
    public function logout() {
        session_destroy();
        redirect('/public/login');
    }

    // Enviar código de recuperación
    public function sendRecoveryCode() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = sanitize($_POST['correo'] ?? '');

            if (empty($correo) || !isValidEmail($correo)) {
                $_SESSION['error'] = "El correo electrónico no es válido";
                redirect('/public/recuperar-password');
                return;
            }

            // Verificar si el correo existe
            $this->userModel->correo = $correo;
            if (!$this->userModel->emailExists()) {
                $_SESSION['error'] = "No existe una cuenta con este correo";
                redirect('/public/recuperar-password');
                return;
            }

            // Generar código de 6 dígitos
            $code = $this->userModel->generateVerificationCode();

            if ($code) {
                // Obtener datos del usuario para personalizar el correo
                $userData = $this->userModel->getById($this->userModel->id);
                $nombre = $userData['nombre'] ?? '';

                // Enviar correo
                if ($this->mailer->sendVerificationCode($correo, $code, $nombre)) {
                    $_SESSION['success'] = "Código enviado a tu correo";
                    $_SESSION['recovery_email'] = $correo;
                    redirect('/public/recuperar-password?step=verify');
                } else {
                    $_SESSION['error'] = "Error al enviar el correo. Intenta de nuevo.";
                    redirect('/public/recuperar-password');
                }
            } else {
                $_SESSION['error'] = "Error al generar el código. Intenta de nuevo.";
                redirect('/public/recuperar-password');
            }
        }
    }

    // Verificar código y cambiar contraseña
    public function verifyAndResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_SESSION['recovery_email'] ?? '';
            $code = sanitize($_POST['code'] ?? '');
            $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
            $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

            // Validaciones
            if (empty($code) || empty($nueva_contrasena)) {
                $_SESSION['error'] = "Todos los campos son requeridos";
                redirect('/public/recuperar-password?step=verify');
                return;
            }

            if (strlen($nueva_contrasena) < 8) {
                $_SESSION['error'] = "La contraseña debe tener al menos 8 caracteres";
                redirect('/public/recuperar-password?step=verify');
                return;
            }

            if ($nueva_contrasena !== $confirmar_contrasena) {
                $_SESSION['error'] = "Las contraseñas no coinciden";
                redirect('/public/recuperar-password?step=verify');
                return;
            }

            // Verificar código
            $this->userModel->correo = $correo;
            if ($this->userModel->verifyCode($code)) {
                // Actualizar contraseña
                $this->userModel->contrasena = $nueva_contrasena;
                if ($this->userModel->updatePassword()) {
                    unset($_SESSION['recovery_email']);
                    $_SESSION['success'] = "Contraseña actualizada exitosamente. Inicia sesión.";
                    redirect('/public/login');
                } else {
                    $_SESSION['error'] = "Error al actualizar la contraseña";
                    redirect('/public/recuperar-password?step=verify');
                }
            } else {
                $_SESSION['error'] = "Código inválido o expirado";
                redirect('/public/recuperar-password?step=verify');
            }
        }
    }
}

// Procesar acciones según la petición
if (isset($_GET['action'])) {
    $controller = new AuthController();
    
    switch ($_GET['action']) {
        case 'register':
            $controller->register();
            break;
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        case 'send-code':
            $controller->sendRecoveryCode();
            break;
        case 'verify-reset':
            $controller->verifyAndResetPassword();
            break;
        default:
            redirect('/public/');
            break;
    }
}
?>