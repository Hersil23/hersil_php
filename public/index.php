<?php
require_once __DIR__ . '/../config/config.php';

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$request = str_replace('/hersil_php/public', '', $request);
$request = strtok($request, '?'); // Remover parámetros GET

// Router
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
    
    case '/buscar':
        require_once __DIR__ . '/../views/products/search.php';
        break;
    
    case '/perfil':
        if (!isLoggedIn()) {
            redirect('/login');
        }
        require_once __DIR__ . '/../views/profile.php';
        break;
    
    // Rutas de administración
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
        echo '<h1>404 - Página no encontrada</h1>';
        echo '<p><a href="' . BASE_URL . '/">Volver al inicio</a></p>';
        break;
}
?>
