<?php
$page_title = "Inicio";
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/category.php';

// Obtener productos y categorías
$productModel = new Product();
$categoryModel = new Category();

$featuredProducts = array_slice($productModel->getAll(), 0, 8);
$categories = $categoryModel->getActiveCategories();
?>

<section class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 dark:from-slate-900 dark:via-slate-800 dark:to-blue-900 text-white py-12 md:py-20">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-8">
            
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 md:mb-6 leading-tight">
                    Los Mejores Productos <span class="text-blue-300">Electrónicos</span>
                </h1>
                <p class="text-base md:text-lg text-blue-100 mb-6 md:mb-8">
                    Descubre nuestra amplia selección de tecnología de última generación. 
                    Calidad garantizada y los mejores precios del mercado.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center md:justify-start">
                    <a href="<?php echo BASE_URL; ?>/public/productos" 
                       class="w-full sm:w-auto bg-white text-blue-900 px-6 md:px-8 py-3 md:py-4 rounded-lg font-semibold hover:bg-blue-50 transition-all transform hover:scale-105 shadow-lg text-center">
                        <i class="fas fa-shopping-bag mr-2"></i>Ver Productos
                    </a>
                    <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/public/register" 
                       class="w-full sm:w-auto bg-blue-700 text-white px-6 md:px-8 py-3 md:py-4 rounded-lg font-semibold hover:bg-blue-600 transition-all border-2 border-white text-center">
                        <i class="fas fa-user-plus mr-2"></i>Registrarse
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="w-full md:w-1/2 flex justify-center">
                <div class="relative">
                    <div class="w-48 h-48 md:w-64 md:h-64 lg:w-80 lg:h-80 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center border-4 border-white/20 shadow-2xl">
                        <i class="fas fa-laptop text-6xl md:text-8xl lg:text-9xl text-white/90"></i>
                    </div>
                    <div class="absolute -top-4 -right-4 w-16 h-16 md:w-20 md:h-20 bg-blue-400 rounded-full animate-pulse"></div>
                    <div class="absolute -bottom-4 -left-4 w-12 h-12 md:w-16 md:h-16 bg-blue-300 rounded-full animate-pulse delay-75"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-8 md:py-12 bg-slate-50 dark:bg-slate-900/50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white dark:bg-slate-800 p-4 md:p-6 rounded-xl shadow-lg text-center transform hover:scale-105 transition-transform">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-box text-xl md:text-2xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                    <?php echo count($productModel->getAll()); ?>+
                </h3>
                <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400">Productos</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 md:p-6 rounded-xl shadow-lg text-center transform hover:scale-105 transition-transform">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-tags text-xl md:text-2xl text-green-600 dark:text-green-400"></i>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-green-600 dark:text-green-400 mb-1">
                    <?php echo count($categories); ?>
                </h3>
                <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400">Categorías</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 md:p-6 rounded-xl shadow-lg text-center transform hover:scale-105 transition-transform">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-star text-xl md:text-2xl text-purple-600 dark:text-purple-400"></i>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-purple-600 dark:text-purple-400 mb-1">100%</h3>
                <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400">Calidad</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 md:p-6 rounded-xl shadow-lg text-center transform hover:scale-105 transition-transform">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-shipping-fast text-xl md:text-2xl text-orange-600 dark:text-orange-400"></i>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-orange-600 dark:text-orange-400 mb-1">24h</h3>
                <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400">Envío</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($categories)): ?>
<section class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-3 md:mb-4">Explora por Categoría</h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base">Encuentra lo que buscas en nuestras categorías especializadas</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo BASE_URL; ?>/public/productos?categoria=<?php echo $category['id']; ?>" 
                   class="group bg-white dark:bg-slate-800 p-4 md:p-6 rounded-xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-2">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center mx-auto mb-3 md:mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-folder text-xl md:text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-center text-sm md:text-base group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        <?php echo htmlspecialchars($category['nombre']); ?>
                    </h3>
                    <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 text-center mt-1 md:mt-2">
                        <?php echo $category['total_productos'] ?? 0; ?> productos
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<section class="py-12 md:py-16 bg-slate-50 dark:bg-slate-900/50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-3 md:mb-4">Productos Destacados</h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm md:text-base">Los productos más populares de nuestra tienda</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-2 transition-all hover:shadow-2xl">
                    <div class="h-48 md:h-56 bg-slate-200 dark:bg-slate-700 overflow-hidden">
                        <?php if (!empty($product['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($product['imagen_url']); ?>" 
                                alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                                class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600">
                                <i class="fas fa-box text-5xl md:text-6xl text-slate-400 dark:text-slate-500"></i>
                            </div>
                        <?php endif; ?>
                    </div>                    
                    
                    <div class="p-4 md:p-5">
                        <span class="inline-block px-2 md:px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs rounded-full mb-2">
                            <?php echo htmlspecialchars($product['categoria_nombre'] ?? 'Sin categoría'); ?>
                        </span>
                        
                        <h3 class="font-bold text-base md:text-lg mb-2 line-clamp-2 min-h-[3rem]">
                            <?php echo htmlspecialchars($product['nombre']); ?>
                        </h3>
                        
                        <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mb-3 md:mb-4 line-clamp-2">
                            <?php echo htmlspecialchars(substr($product['descripcion'], 0, 80)) . '...'; ?>
                        </p>
                        
                        <div class="flex items-center justify-between mb-3 md:mb-4">
                            <span class="text-xl md:text-2xl font-bold text-blue-600 dark:text-blue-400">
                                $<?php echo number_format($product['precio'], 2); ?>
                            </span>
                            <span class="text-xs md:text-sm <?php echo $product['stock'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                                <i class="fas fa-box mr-1"></i>
                                <?php echo $product['stock'] > 0 ? 'Stock: ' . $product['stock'] : 'Agotado'; ?>
                            </span>
                        </div>
                        
                        <a href="<?php echo BASE_URL; ?>/public/producto?id=<?php echo $product['id']; ?>" 
                           class="block w-full bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 md:py-3 rounded-lg transition-colors font-semibold text-sm md:text-base text-center">
                            <i class="fas fa-eye mr-2"></i>Ver Detalles
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8 md:mt-12">
            <a href="<?php echo BASE_URL; ?>/public/productos" 
               class="inline-block bg-blue-800 hover:bg-blue-900 text-white px-6 md:px-8 py-3 md:py-4 rounded-lg font-semibold transition-all transform hover:scale-105 shadow-lg">
                Ver Todos los Productos
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="bg-gradient-to-br from-blue-800 to-blue-600 dark:from-blue-900 dark:to-slate-900 rounded-2xl p-6 md:p-12 text-white text-center shadow-2xl">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-3 md:mb-4">¿Listo para comprar?</h2>
            <p class="text-base md:text-lg text-blue-100 mb-6 md:mb-8 max-w-2xl mx-auto">
                Regístrate ahora y obtén acceso a ofertas exclusivas y los mejores productos del mercado
            </p>
            <a href="<?php echo BASE_URL; ?>/public/register" 
               class="inline-block bg-white text-blue-900 px-6 md:px-8 py-3 md:py-4 rounded-lg font-bold hover:bg-blue-50 transition-all transform hover:scale-105 shadow-lg text-sm md:text-base">
                <i class="fas fa-rocket mr-2"></i>Comenzar Ahora
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>