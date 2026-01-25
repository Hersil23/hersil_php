<?php
$page_title = "Dashboard - Panel Admin";
require_once __DIR__ . '/../layouts/header.php';

// Verificar acceso admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "No tienes permiso para acceder a esta página";
    redirect('/public/');
    exit;
}

// Cargar modelos
require_once __DIR__ . '/../../models/product.php';
require_once __DIR__ . '/../../models/category.php';
require_once __DIR__ . '/../../models/user.php';

$productModel = new Product();
$categoryModel = new Category();
$userModel = new User();

// Obtener estadísticas
$products = $productModel->getAll();
$categories = $categoryModel->getAll();
$users = $userModel->getAllUsers();
$lowStockProducts = $productModel->getLowStock();

// Calcular totales
$totalProducts = count($products);
$totalCategories = count($categories);
$totalUsers = count($users);
$totalLowStock = count($lowStockProducts);

// Calcular valor total del inventario
$inventoryValue = 0;
$totalStock = 0;
foreach ($products as $product) {
    $inventoryValue += $product['precio'] * $product['stock'];
    $totalStock += $product['stock'];
}

// Contar usuarios por rol
$totalAdmins = 0;
$totalRegularUsers = 0;
foreach ($users as $user) {
    if ($user['rol'] === 'administrador') {
        $totalAdmins++;
    } else {
        $totalRegularUsers++;
    }
}

// Productos por categoría para el gráfico
$productsByCategory = $productModel->countByCategory();
?>

<div class="bg-slate-50 dark:bg-slate-900/50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <!-- Encabezado -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 flex items-center">
                <i class="fas fa-tachometer-alt mr-3 text-blue-600 dark:text-blue-400"></i>
                Dashboard
            </h1>
            <p class="text-slate-600 dark:text-slate-400">
                Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Admin'); ?></strong>. 
                Aquí tienes un resumen de tu tienda.
            </p>
        </div>

        <!-- Tarjetas de estadísticas principales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Total Productos -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Total Productos</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $totalProducts; ?></h3>
                        <p class="text-xs text-slate-400 mt-1"><?php echo $totalStock; ?> unidades en stock</p>
                    </div>
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-box text-2xl text-white"></i>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/public/admin/productos" class="mt-4 text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                    Ver productos <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Total Categorías -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Categorías</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $totalCategories; ?></h3>
                        <p class="text-xs text-slate-400 mt-1">Categorías activas</p>
                    </div>
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tags text-2xl text-white"></i>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/public/admin/categorias" class="mt-4 text-sm text-green-600 dark:text-green-400 hover:underline flex items-center">
                    Ver categorías <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Total Usuarios -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Usuarios</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $totalUsers; ?></h3>
                        <p class="text-xs text-slate-400 mt-1"><?php echo $totalAdmins; ?> admin, <?php echo $totalRegularUsers; ?> usuarios</p>
                    </div>
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/public/admin/usuarios" class="mt-4 text-sm text-purple-600 dark:text-purple-400 hover:underline flex items-center">
                    Ver usuarios <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <!-- Valor del Inventario -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Valor Inventario</p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">$<?php echo number_format($inventoryValue, 2); ?></h3>
                        <p class="text-xs text-slate-400 mt-1">Valor total en stock</p>
                    </div>
                    <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-2xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Productos con Stock Bajo -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        Productos con Stock Bajo
                        <?php if ($totalLowStock > 0): ?>
                        <span class="ml-2 px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs rounded-full">
                            <?php echo $totalLowStock; ?> productos
                        </span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (empty($lowStockProducts)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                            <p class="text-slate-600 dark:text-slate-400">¡Excelente! No hay productos con stock bajo.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-sm text-slate-500 dark:text-slate-400">
                                        <th class="pb-3">Producto</th>
                                        <th class="pb-3">Categoría</th>
                                        <th class="pb-3">Stock</th>
                                        <th class="pb-3">Precio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                    <?php foreach (array_slice($lowStockProducts, 0, 5) as $product): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                        <td class="py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center">
                                                    <?php if (!empty($product['imagen_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product['imagen_url']); ?>" class="w-full h-full object-cover rounded-lg">
                                                    <?php else: ?>
                                                        <i class="fas fa-box text-slate-400"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="font-medium"><?php echo htmlspecialchars($product['nombre']); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-sm text-slate-600 dark:text-slate-400">
                                            <?php echo htmlspecialchars($product['categoria_nombre'] ?? 'Sin categoría'); ?>
                                        </td>
                                        <td class="py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $product['stock'] == 0 ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400'; ?>">
                                                <?php echo $product['stock']; ?> unid.
                                            </span>
                                        </td>
                                        <td class="py-3 font-semibold text-blue-600 dark:text-blue-400">
                                            $<?php echo number_format($product['precio'], 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($totalLowStock > 5): ?>
                        <div class="mt-4 text-center">
                            <a href="<?php echo BASE_URL; ?>/public/admin/productos?stock=bajo" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                Ver todos los <?php echo $totalLowStock; ?> productos con stock bajo
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="<?php echo BASE_URL; ?>/public/admin/productos?action=nuevo" 
                       class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">Nuevo Producto</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Agregar al inventario</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-400 group-hover:text-blue-600"></i>
                    </a>

                    <a href="<?php echo BASE_URL; ?>/public/admin/categorias?action=nuevo" 
                       class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors group">
                        <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-folder-plus text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">Nueva Categoría</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Organizar productos</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-400 group-hover:text-green-600"></i>
                    </a>

                    <a href="<?php echo BASE_URL; ?>/public/" 
                       class="flex items-center gap-3 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors group">
                        <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">Ver Tienda</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Ir al sitio público</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-slate-400 group-hover:text-purple-600"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Productos por Categoría -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-bold flex items-center">
                    <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                    Productos por Categoría
                </h3>
            </div>
            <div class="p-6">
                <?php if (empty($productsByCategory)): ?>
                    <p class="text-center text-slate-500 dark:text-slate-400 py-8">No hay datos disponibles</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php 
                        $colors = ['blue', 'green', 'purple', 'yellow', 'red', 'indigo', 'pink', 'teal'];
                        $i = 0;
                        foreach ($productsByCategory as $cat): 
                            $color = $colors[$i % count($colors)];
                            $percentage = $totalProducts > 0 ? round(($cat['total'] / $totalProducts) * 100) : 0;
                        ?>
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium"><?php echo htmlspecialchars($cat['nombre']); ?></span>
                                <span class="text-sm text-slate-500 dark:text-slate-400"><?php echo $cat['total']; ?> productos</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                                <div class="bg-<?php echo $color; ?>-500 h-2 rounded-full transition-all duration-500" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo $percentage; ?>% del total</p>
                        </div>
                        <?php $i++; endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>