<?php
$page_title = "Gestión de Productos";
require_once __DIR__ . '/../layouts/header.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "No tienes permiso para acceder a esta página";
    redirect('/public/');
    exit;
}

require_once __DIR__ . '/../../models/product.php';
require_once __DIR__ . '/../../models/category.php';

$productModel = new Product();
$categoryModel = new Category();

$busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';
$filtro_categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

if ($busqueda) {
    $products = $productModel->search($busqueda);
} elseif ($filtro_categoria) {
    $products = $productModel->getByCategory($filtro_categoria);
} else {
    $products = $productModel->getAll();
}

$categories = $categoryModel->getAll();
?>

<div class="bg-slate-50 dark:bg-slate-900/50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 flex items-center">
                <i class="fas fa-box-open mr-3 text-blue-600 dark:text-blue-400"></i>
                Gestión de Productos
            </h1>
            <p class="text-slate-600 dark:text-slate-400">Administra el inventario de productos</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 mb-6 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                
                <form method="GET" class="flex-1 w-full md:w-auto">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="busqueda" 
                            placeholder="Buscar productos..."
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            class="w-full px-4 py-3 pl-10 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-slate-100"
                        >
                        <i class="fas fa-search absolute left-3 top-4 text-slate-400"></i>
                    </div>
                </form>

                <div class="flex gap-3 w-full md:w-auto">
                    <select onchange="window.location.href='?categoria=' + this.value + '<?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>'" 
                            class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button onclick="addProduct()" 
                            class="px-6 py-3 bg-blue-800 hover:bg-blue-900 text-white rounded-lg transition-colors font-semibold whitespace-nowrap">
                        <i class="fas fa-plus mr-2"></i>Nuevo Producto
                    </button>
                    
                    <?php if ($busqueda || $filtro_categoria): ?>
                    <a href="<?php echo BASE_URL; ?>/public/admin/productos" 
                       class="px-4 py-3 bg-slate-200 dark:bg-slate-700 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-12 text-center border border-slate-200 dark:border-slate-700">
                <i class="fas fa-box-open text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">No se encontraron productos</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-4">
                    <?php echo $busqueda ? 'Intenta con otros términos de búsqueda' : 'Comienza agregando productos al inventario'; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100 dark:bg-slate-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Imagen</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Producto</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Categoría</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Precio</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold">Stock</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-700">
                                        <?php if (!empty($product['imagen_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['imagen_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-box text-2xl text-slate-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($product['nombre']); ?></p>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1">
                                            <?php echo htmlspecialchars(substr($product['descripcion'], 0, 50)) . '...'; ?>
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs rounded-full">
                                        <?php echo htmlspecialchars($product['categoria_nombre'] ?? 'Sin categoría'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-blue-600 dark:text-blue-400">
                                        $<?php echo number_format($product['precio'], 2); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $product['stock'] > 10 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($product['stock'] > 0 ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'); ?>">
                                        <?php echo $product['stock']; ?> unid.
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="viewProduct(<?php echo $product['id']; ?>)" 
                                                class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors" 
                                                title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editProduct(<?php echo $product['id']; ?>)" 
                                                class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors" 
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['nombre']); ?>')" 
                                                class="p-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-600">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Total de productos: <strong><?php echo count($products); ?></strong>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function addProduct() {
    alert('Agregar nuevo producto\n(Funcionalidad pendiente)');
}

function viewProduct(id) {
    window.location.href = '<?php echo BASE_URL; ?>/public/productos';
}

function editProduct(id) {
    alert('Editar producto #' + id + '\n(Funcionalidad pendiente)');
}

function deleteProduct(id, nombre) {
    if (confirm('¿Estás seguro de eliminar el producto "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
        alert('Eliminar producto #' + id + '\n(Funcionalidad pendiente)');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>