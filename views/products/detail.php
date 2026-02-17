<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/product.php';

// Cargar producto ANTES del header para poder usar sus datos en OG meta tags
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Producto no encontrado";
    redirect('/public/productos');
    exit;
}

$productModel = new Product();
$product = $productModel->getById(intval($_GET['id']));

if (!$product) {
    $_SESSION['error'] = "Producto no encontrado";
    redirect('/public/productos');
    exit;
}

// Definir variable para OG meta tags en el header
$og_product = [
    'title' => $product['nombre'],
    'description' => substr($product['descripcion'], 0, 200),
    'image' => $product['imagen_url'] ?? '',
    'url' => BASE_URL . '/producto?id=' . $product['id'],
    'price' => number_format($product['precio'], 2)
];

$page_title = htmlspecialchars($product['nombre']);
require_once __DIR__ . '/../layouts/header.php';

// Crear mensaje para WhatsApp
$whatsapp_message = "Hola, estoy interesado en el producto: *" . $product['nombre'] . "* - Precio: $" . number_format($product['precio'], 2);
$whatsapp_url = "https://wa.me/584145116337?text=" . urlencode($whatsapp_message);
$product_url = BASE_URL . '/producto?id=' . $product['id'];
?>

<div class="bg-slate-50 dark:bg-slate-900/50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="<?php echo BASE_URL; ?>/public/" class="text-blue-600 dark:text-blue-400 hover:underline">Inicio</a></li>
                <li><i class="fas fa-chevron-right text-slate-400 text-xs"></i></li>
                <li><a href="<?php echo BASE_URL; ?>/public/productos" class="text-blue-600 dark:text-blue-400 hover:underline">Productos</a></li>
                <li><i class="fas fa-chevron-right text-slate-400 text-xs"></i></li>
                <li class="text-slate-600 dark:text-slate-400"><?php echo htmlspecialchars($product['nombre']); ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Imagen del producto -->
            <?php
            $images = [];
            if (!empty($product['imagen_url'])) $images[] = $product['imagen_url'];
            if (!empty($product['imagen_url_2'])) $images[] = $product['imagen_url_2'];
            if (!empty($product['imagen_url_3'])) $images[] = $product['imagen_url_3'];
            ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8">
                <div class="aspect-square bg-slate-100 dark:bg-slate-700 rounded-xl overflow-hidden">
                    <?php if (!empty($images)): ?>
                        <img id="mainProductImage" src="<?php echo htmlspecialchars($images[0]); ?>"
                             alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                             class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-box text-9xl text-slate-400 dark:text-slate-500"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <div class="flex gap-3 mt-4" id="thumbnailsContainer">
                    <?php foreach ($images as $index => $img): ?>
                    <button type="button" data-img="<?php echo htmlspecialchars($img); ?>"
                            class="thumb-btn w-20 h-20 rounded-lg overflow-hidden border-2 cursor-pointer hover:opacity-80 transition-all <?php echo $index === 0 ? 'border-blue-500' : 'border-slate-300 dark:border-slate-600'; ?>">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Imagen <?php echo $index + 1; ?>" class="w-full h-full object-cover pointer-events-none">
                    </button>
                    <?php endforeach; ?>
                </div>
                <script>
                document.getElementById('thumbnailsContainer').addEventListener('click', function(e) {
                    var btn = e.target.closest('.thumb-btn');
                    if (!btn) return;
                    var imgUrl = btn.getAttribute('data-img');
                    document.getElementById('mainProductImage').src = imgUrl;
                    var buttons = this.querySelectorAll('.thumb-btn');
                    for (var i = 0; i < buttons.length; i++) {
                        buttons[i].classList.remove('border-blue-500');
                        buttons[i].classList.add('border-slate-300', 'dark:border-slate-600');
                    }
                    btn.classList.remove('border-slate-300', 'dark:border-slate-600');
                    btn.classList.add('border-blue-500');
                });
                </script>
                <?php endif; ?>
            </div>

            <!-- Información del producto -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8">
                
                <!-- Categoría -->
                <span class="inline-block px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-sm rounded-full mb-4">
                    <i class="fas fa-tag mr-2"></i><?php echo htmlspecialchars($product['categoria_nombre'] ?? 'Sin categoría'); ?>
                </span>

                <!-- Nombre -->
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <?php echo htmlspecialchars($product['nombre']); ?>
                </h1>

                <!-- Compartir -->
                <div class="flex items-center gap-2 mb-4">
                    <button onclick="shareProduct('<?php echo htmlspecialchars($product_url, ENT_QUOTES); ?>', '<?php echo htmlspecialchars(addslashes($product['nombre']), ENT_QUOTES); ?>')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                            title="Copiar enlace">
                        <i class="fas fa-link"></i>
                        <span>Copiar enlace</span>
                    </button>
                    <button onclick="shareWhatsApp('<?php echo htmlspecialchars($product_url, ENT_QUOTES); ?>', '<?php echo htmlspecialchars(addslashes($product['nombre']), ENT_QUOTES); ?>')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                            title="Compartir por WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                        <span>Compartir</span>
                    </button>
                </div>

                <!-- Precio y Stock -->
                <div class="flex items-center gap-6 mb-6 pb-6 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Precio</p>
                        <p class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                            $<?php echo number_format($product['precio'], 2); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">Disponibilidad</p>
                        <p class="text-lg font-semibold <?php echo $product['stock'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>">
                            <i class="fas fa-box mr-2"></i>
                            <?php if ($product['stock'] > 10): ?>
                                En stock (<?php echo $product['stock']; ?> unidades)
                            <?php elseif ($product['stock'] > 0): ?>
                                Últimas unidades (<?php echo $product['stock']; ?>)
                            <?php else: ?>
                                Agotado
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4">Descripción</h2>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($product['descripcion'])); ?>
                    </p>
                </div>

                <!-- Características -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4">Información adicional</h2>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Garantía de calidad</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-shipping-fast text-blue-500 mr-3"></i>
                            <span>Envío en 24-48 horas</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-purple-500 mr-3"></i>
                            <span>Compra segura</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-undo text-orange-500 mr-3"></i>
                            <span>30 días de devolución</span>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="space-y-3">
                    <?php if ($product['stock'] > 0): ?>
                        <a href="<?php echo $whatsapp_url; ?>" 
                           target="_blank"
                           class="flex items-center justify-center w-full bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-lg font-bold text-lg transition-all transform hover:scale-105 shadow-lg">
                            <i class="fab fa-whatsapp mr-2 text-2xl"></i>Comprar por WhatsApp
                        </a>
                    <?php else: ?>
                        <button disabled class="w-full bg-slate-400 text-white px-6 py-4 rounded-lg font-bold text-lg cursor-not-allowed">
                            <i class="fas fa-times-circle mr-2"></i>Producto Agotado
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>/public/productos" 
                       class="block w-full bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white px-6 py-4 rounded-lg font-bold text-lg text-center transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>Volver a Productos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>