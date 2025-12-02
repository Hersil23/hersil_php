<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar configuración
require_once __DIR__ . '/../../config/config.php';

// Obtener usuario actual
$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();

// Obtener la página actual para marcar el menú activo
$current_page = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Hersil Shop'; ?> - Tienda de Electrónica</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuración personalizada de Tailwind -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#1E40AF',
                            dark: '#1E3A8A',
                        },
                        accent: '#3B82F6',
                    }
                }
            }
        }
    </script>
    
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/styles.css">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300">
    
    <!-- Navbar -->
    <nav class="bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 transition-colors duration-300">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>/" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-800 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white">Hersil Shop</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?php echo BASE_URL; ?>/" 
                       class="px-4 py-2 rounded-lg transition-colors <?php echo (strpos($current_page, '/inicio') !== false || $current_page === '/' || strpos($current_page, '/home') !== false) ? 'bg-blue-800 text-white' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'; ?>">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/productos" 
                       class="px-4 py-2 rounded-lg transition-colors <?php echo strpos($current_page, '/productos') !== false ? 'bg-blue-800 text-white' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'; ?>">
                        <i class="fas fa-shopping-bag mr-2"></i>Productos
                    </a>

                    <?php if ($isLoggedIn): ?>
                        <?php if ($isAdmin): ?>
                            <!-- Menú Admin -->
                            <div class="relative group">
                                <button class="px-4 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                                    <i class="fas fa-cog mr-2"></i>Administración
                                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                <div class="absolute left-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                    <a href="<?php echo BASE_URL; ?>/admin/usuarios" class="block px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <i class="fas fa-users mr-2"></i>Usuarios
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/categorias" class="block px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <i class="fas fa-tags mr-2"></i>Categorías
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/productos" class="block px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <i class="fas fa-box mr-2"></i>Productos
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Perfil Usuario -->
                        <div class="relative group">
                            <button class="px-4 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                                <i class="fas fa-user-circle mr-2"></i><?php echo $currentUser['nombre']; ?>
                                <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                                    <p class="font-semibold"><?php echo $currentUser['nombre'] . ' ' . $currentUser['apellido']; ?></p>
                                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $currentUser['correo']; ?></p>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/perfil" class="block px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <i class="fas fa-user mr-2"></i>Mi Perfil
                                </a>
                                <a href="<?php echo BASE_URL; ?>/controllers/authcontroller.php?action=logout" class="block px-4 py-3 text-red-600 dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Botones Login/Registro -->
                        <a href="<?php echo BASE_URL; ?>/login" 
                           class="px-4 py-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                        </a>
                        <a href="<?php echo BASE_URL; ?>/register" 
                           class="px-4 py-2 bg-blue-800 hover:bg-blue-900 text-white rounded-lg transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Registrarse
                        </a>
                    <?php endif; ?>

                    <!-- Toggle Tema -->
                    <button onclick="toggleTheme()" id="theme-toggle" 
                            class="ml-2 p-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                        <span id="theme-icon">
                            <i class="fas fa-moon text-lg"></i>
                        </span>
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <a href="<?php echo BASE_URL; ?>/" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                    <i class="fas fa-home mr-2"></i>Inicio
                </a>
                <a href="<?php echo BASE_URL; ?>/productos" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                    <i class="fas fa-shopping-bag mr-2"></i>Productos
                </a>
                
                <?php if ($isLoggedIn): ?>
                    <?php if ($isAdmin): ?>
                        <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>
                        <p class="px-4 py-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Administración</p>
                        <a href="<?php echo BASE_URL; ?>/admin/usuarios" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                            <i class="fas fa-users mr-2"></i>Usuarios
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/categorias" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                            <i class="fas fa-tags mr-2"></i>Categorías
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/productos" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                            <i class="fas fa-box mr-2"></i>Productos
                        </a>
                    <?php endif; ?>
                    
                    <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>
                    <a href="<?php echo BASE_URL; ?>/perfil" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                        <i class="fas fa-user mr-2"></i>Mi Perfil
                    </a>
                    <a href="<?php echo BASE_URL; ?>/controllers/authcontroller.php?action=logout" class="block px-4 py-3 text-red-600 dark:text-red-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register" class="block px-4 py-3 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mensajes Flash -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-400 p-4 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 p-4 rounded-lg" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-400 p-4 rounded-lg" role="alert">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle mr-3 mt-1"></i>
                    <div>
                        <p class="font-semibold mb-2">Se encontraron los siguientes errores:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="min-h-screen">