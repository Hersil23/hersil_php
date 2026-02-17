<?php
$page_title = "Productos";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../models/product.php';
require_once __DIR__ . '/../../models/category.php';

$productModel = new Product();
$categoryModel = new Category();

$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

if ($busqueda) {
    $products = $productModel->search($busqueda);
} elseif ($categoria_id) {
    $products = $productModel->getByCategory($categoria_id);
} else {
    $products = $productModel->getAll();
}

$categories = $categoryModel->getActiveCategories();
?>

<div class="bg-slate-50 dark:bg-slate-900/50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Nuestros Productos</h1>
            <p class="text-slate-600 dark:text-slate-400">Explora nuestra amplia selección de productos electrónicos</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            
            <aside class="lg:w-64 space-y-6">
                
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700">
                    <h3 class="font-bold text-lg mb-4 flex items-center">
                        <i class="fas fa-search mr-2 text-blue-600 dark:text-blue-400"></i>
                        Buscar
                    </h3>
                    <form method="GET" action="<?php echo BASE_URL; ?>/productos" class="space-y-3">
                        <input 
                            type="text" 
                            name="busqueda" 
                            placeholder="Buscar productos..."
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-slate-100"
                        >
                        <button type="submit" class="w-full bg-blue-800 hover:bg-blue-900 text-white py-2 rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                        <?php if ($busqueda): ?>
                            <a href="<?php echo BASE_URL; ?>/productos" class="block text-center text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600">
                                Limpiar búsqueda
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700">
                    <h3 class="font-bold text-lg mb-4 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-600 dark:text-blue-400"></i>
                        Categorías
                    </h3>
                    <div class="space-y-2">
                        <a href="<?php echo BASE_URL; ?>/productos" 
                           class="block px-4 py-2 rounded-lg transition-colors <?php echo !$categoria_id ? 'bg-blue-800 text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-700'; ?>">
                            <i class="fas fa-th mr-2"></i>Todas
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="<?php echo BASE_URL; ?>/productos?categoria=<?php echo $category['id']; ?>" 
                               class="block px-4 py-2 rounded-lg transition-colors <?php echo $categoria_id == $category['id'] ? 'bg-blue-800 text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-700'; ?>">
                                <i class="fas fa-folder mr-2"></i><?php echo htmlspecialchars($category['nombre']); ?>
                                <span class="text-xs opacity-75">(<?php echo $category['total_productos'] ?? 0; ?>)</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>

            <main class="flex-1">
                
                <?php if ($busqueda): ?>
                    <div class="mb-6 bg-blue-100 dark:bg-blue-900/30 border-l-4 border-blue-500 p-4 rounded-lg">
                        <p class="text-blue-700 dark:text-blue-400">
                            <i class="fas fa-search mr-2"></i>
                            Resultados para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
                            (<?php echo count($products); ?> productos)
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (empty($products)): ?>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-12 text-center border border-slate-200 dark:border-slate-700">
                        <i class="fas fa-box-open text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">No se encontraron productos</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-4">
                            <?php if ($busqueda): ?>
                                Intenta con otros términos de búsqueda
                            <?php else: ?>
                                No hay productos en esta categoría
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo BASE_URL; ?>/productos" class="inline-block bg-blue-800 hover:bg-blue-900 text-white px-6 py-2 rounded-lg transition-colors">
                            Ver todos los productos
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-all hover:shadow-2xl border border-slate-200 dark:border-slate-700">
                                
                                <div class="h-48 bg-slate-200 dark:bg-slate-700 overflow-hidden">
                                    <?php if (!empty($product['imagen_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['imagen_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                                             class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600">
                                            <i class="fas fa-box text-5xl text-slate-400 dark:text-slate-500"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="p-5">
                                    <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs rounded-full mb-2">
                                        <?php echo htmlspecialchars($product['categoria_nombre'] ?? 'Sin categoría'); ?>
                                    </span>
                                    
                                    <h3 class="font-bold text-lg mb-2 line-clamp-2 min-h-[3.5rem]">
                                        <?php echo htmlspecialchars($product['nombre']); ?>
                                    </h3>
                                    
                                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2">
                                        <?php echo htmlspecialchars(substr($product['descripcion'], 0, 80)) . '...'; ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            $<?php echo number_format($product['precio'], 2); ?>
                                        </span>
                                        <span class="text-sm <?php echo $product['stock'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                            <i class="fas fa-box mr-1"></i>
                                            <?php echo $product['stock'] > 0 ? 'Stock: ' . $product['stock'] : 'Agotado'; ?>
                                        </span>
                                    </div>
                                    
                                    <a href="<?php echo BASE_URL; ?>/producto?id=<?php echo $product['id']; ?>"
                                        class="block w-full bg-blue-800 hover:bg-blue-900 text-white px-4 py-3 rounded-lg transition-colors font-semibold text-center">
                                        <i class="fas fa-eye mr-2"></i>Ver Detalles
                                    </a>
                                    <div class="flex gap-2 mt-2">
                                        <button onclick="shareProduct('<?php echo BASE_URL; ?>/producto?id=<?php echo $product['id']; ?>', '<?php echo htmlspecialchars(addslashes($product['nombre']), ENT_QUOTES); ?>')"
                                                class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                                                title="Copiar enlace">
                                            <i class="fas fa-link"></i>
                                            <span>Copiar</span>
                                        </button>
                                        <button onclick="shareWhatsApp('<?php echo BASE_URL; ?>/producto?id=<?php echo $product['id']; ?>', '<?php echo htmlspecialchars(addslashes($product['nombre']), ENT_QUOTES); ?>')"
                                                class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                                                title="Compartir por WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                            <span>WhatsApp</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>