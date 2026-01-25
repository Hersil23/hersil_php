<?php
// Cargar configuracion global
require_once __DIR__ . '/../config/config.php';

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];

// Limpiar la ruta segun el entorno
if (strpos($request, '/hersil_php/public') !== false) {
    $request = str_replace('/hersil_php/public', '', $request);
} elseif (strpos($request, '/public') !== false) {
    $request = str_replace('/public', '', $request);
}

$request = strtok($request, '?');

// Router principal
switch ($request) {
    case '/':
    case '/home':
    case '/inicio':
        require_once __DIR__ . '/../views/home.php';
        break;
        
    case '/login':
        require_once __DIR__ . '/../views/auth/login.php';
        break;
        
    case '/register':
        require_once __DIR__ . '/../views/auth/register.php';
        break;
        
    case '/recuperar-password':
        require_once __DIR__ . '/../views/auth/recover-password.php';
        break;
        
    case '/productos':
        require_once __DIR__ . '/../views/products/index.php';
        break;
        
    case '/producto':
        require_once __DIR__ . '/../views/products/detail.php';
        break;
        
    case '/buscar':
        require_once __DIR__ . '/../views/products/search.php';
        break;
        
    case '/perfil':
        if (!isLoggedIn()) {
            redirect('/login');
        }
        require_once __DIR__ . '/../views/profile.php';
        break;
        
    case '/logout':
        require_once __DIR__ . '/../controllers/authcontroller.php';
        $authController = new AuthController();
        $authController->logout();
        exit();
        break;
        
    // Rutas de administracion
    case '/admin':
    case '/admin/':
    case '/admin/dashboard':
        if (!isAdmin()) {
            redirect('/');
        }
        require_once __DIR__ . '/../views/admin/dashboard.php';
        break;
        
    case '/admin/usuarios':
        if (!isAdmin()) {
            redirect('/');
        }
        require_once __DIR__ . '/../views/admin/users.php';
        break;
        
    case '/admin/categorias':
        if (!isAdmin()) {
            redirect('/');
        }
        require_once __DIR__ . '/../views/admin/categories.php';
        break;
        
    case '/admin/productos':
        if (!isAdmin()) {
            redirect('/');
        }
        require_once __DIR__ . '/../views/admin/products.php';
        break;
        
    default:
        http_response_code(404);
        echo '<h1>404 - Pagina no encontrada</h1>';
        echo '<p><a href="' . BASE_URL . '/">Volver al inicio</a></p>';
        break;
}