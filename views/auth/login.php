<?php
$page_title = "Iniciar Sesión";

// Si ya está logueado, mostrar mensaje
if (isLoggedIn()) {
    require_once __DIR__ . '/../layouts/header.php';
    ?>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-900/50">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                    <i class="fas fa-exclamation-triangle text-white text-4xl"></i>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-4">
                    Ya tienes una sesión activa
                </h2>
                <p class="text-slate-600 dark:text-slate-400 mb-8">
                    Ya has iniciado sesión como <strong><?php echo $_SESSION['user_nombre']; ?></strong>. 
                    Debes cerrar sesión antes de iniciar con otra cuenta.
                </p>
                <div class="space-y-3">
                    <a href="<?php echo BASE_URL; ?>/public/" class="block w-full bg-blue-800 hover:bg-blue-900 text-white font-bold py-3 px-4 rounded-lg transition-all">
                        <i class="fas fa-home mr-2"></i>Ir al inicio
                    </a>
                    <a href="<?php echo BASE_URL; ?>/controllers/authcontroller.php?action=logout" class="block w-full bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white font-semibold py-3 px-4 rounded-lg transition-all">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../layouts/footer.php';
    exit();
}

require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Contenedor principal - Mobile First -->
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-900/50">
    <div class="max-w-md w-full space-y-8">
        
        <!-- Logo y título -->
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-blue-800 to-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                <i class="fas fa-bolt text-white text-4xl"></i>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-2">
                Bienvenido de nuevo
            </h2>
            <p class="text-slate-600 dark:text-slate-400">
                Inicia sesión para continuar en Hersil Shop
            </p>
        </div>

        <!-- Formulario de Login -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 space-y-6 border border-slate-200 dark:border-slate-700">
            
            <form action="<?php echo BASE_URL; ?>/controllers/authcontroller.php?action=login" method="POST" class="space-y-6">
                
                <!-- Email -->
                <div>
                    <label for="correo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Correo electrónico
                    </label>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        required
                        value="<?php echo isset($_SESSION['form_data']['correo']) ? htmlspecialchars($_SESSION['form_data']['correo']) : ''; ?>"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        placeholder="tu@email.com"
                    >
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="contrasena" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="fas fa-lock mr-2"></i>Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="contrasena" 
                        name="contrasena" 
                        required
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Recordar y Olvidé contraseña -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600">
                        <span class="ml-2 text-slate-600 dark:text-slate-400">Recordarme</span>
                    </label>
                    <a href="<?php echo BASE_URL; ?>/public/recuperar-password" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Botón Submit -->
                <button 
                    type="submit"
                    class="w-full bg-blue-800 hover:bg-blue-900 text-white font-bold py-3 px-4 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                </button>
            </form>

            <!-- Separador -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-300 dark:border-slate-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                        ¿No tienes cuenta?
                    </span>
                </div>
            </div>

            <!-- Botón Registro -->
            <a 
                href="<?php echo BASE_URL; ?>/public/register"
                class="w-full block text-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-900 dark:text-white font-semibold py-3 px-4 rounded-lg transition-all border-2 border-slate-300 dark:border-slate-600"
            >
                <i class="fas fa-user-plus mr-2"></i>Crear cuenta nueva
            </a>
        </div>

        <!-- Link volver al inicio -->
        <div class="text-center">
            <a href="<?php echo BASE_URL; ?>/public/" class="text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Volver al inicio
            </a>
        </div>
    </div>
</div>

<?php 
// Limpiar datos del formulario
unset($_SESSION['form_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>