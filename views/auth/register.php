<?php
$page_title = "Registro";
require_once __DIR__ . '/../layouts/header.php';

if (isLoggedIn()) {
    redirect('/public/');
}
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-900/50">
    <div class="max-w-md w-full space-y-8">
        
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-blue-800 to-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                <i class="fas fa-user-plus text-white text-4xl"></i>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white mb-2">
                Crear cuenta
            </h2>
            <p class="text-slate-600 dark:text-slate-400">
                Únete a Hersil Shop y disfruta de nuestros productos
            </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 space-y-6 border border-slate-200 dark:border-slate-700">
            
            <form action="<?php echo BASE_URL; ?>/controllers/authcontroller.php?action=register" method="POST" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <i class="fas fa-user mr-2"></i>Nombre
                        </label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required
                            value="<?php echo isset($_SESSION['form_data']['nombre']) ? htmlspecialchars($_SESSION['form_data']['nombre']) : ''; ?>"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                            placeholder="Juan"
                        >
                    </div>

                    <div>
                        <label for="apellido" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <i class="fas fa-user mr-2"></i>Apellido
                        </label>
                        <input 
                            type="text" 
                            id="apellido" 
                            name="apellido" 
                            required
                            value="<?php echo isset($_SESSION['form_data']['apellido']) ? htmlspecialchars($_SESSION['form_data']['apellido']) : ''; ?>"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                            placeholder="Pérez"
                        >
                    </div>
                </div>

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

                <div>
                    <label for="contrasena" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="fas fa-lock mr-2"></i>Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="contrasena" 
                        name="contrasena" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        placeholder="Mínimo 8 caracteres"
                    >
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        La contraseña debe tener al menos 8 caracteres
                    </p>
                </div>

                <div>
                    <label for="confirmar_contrasena" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="fas fa-lock mr-2"></i>Confirmar contraseña
                    </label>
                    <input 
                        type="password" 
                        id="confirmar_contrasena" 
                        name="confirmar_contrasena" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        placeholder="Repite tu contraseña"
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-blue-800 hover:bg-blue-900 text-white font-bold py-3 px-4 rounded-lg transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
                >
                    <i class="fas fa-user-plus"></i>
                    <span>Crear cuenta</span>
                </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-300 dark:border-slate-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                        ¿Ya tienes cuenta?
                    </span>
                </div>
            </div>

            <a 
                href="<?php echo BASE_URL; ?>/public/login"
                class="w-full block text-center bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-900 dark:text-white font-semibold py-3 px-4 rounded-lg transition-all border-2 border-slate-300 dark:border-slate-600"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar sesión
            </a>
        </div>

        <div class="text-center">
            <a href="<?php echo BASE_URL; ?>/public/" class="text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Volver al inicio
            </a>
        </div>
    </div>
</div>

<?php 
unset($_SESSION['form_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>
