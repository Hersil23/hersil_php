<?php
$page_title = "Gestión de Productos";
require_once __DIR__ . '/../layouts/header.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "No tienes permiso para acceder a esta página";
    redirect('/');
    exit;
}

require_once __DIR__ . '/../../models/product.php';
require_once __DIR__ . '/../../models/category.php';
require_once __DIR__ . '/../../utils/Security.php';

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
$csrf_token = Security::generateCSRFToken();
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

                    <button onclick="openModal()" 
                            class="px-6 py-3 bg-blue-800 hover:bg-blue-900 text-white rounded-lg transition-colors font-semibold whitespace-nowrap">
                        <i class="fas fa-plus mr-2"></i>Nuevo Producto
                    </button>
                    
                    <?php if ($busqueda || $filtro_categoria): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/productos" 
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
                                        <button onclick="editProduct(<?php echo $product['id']; ?>)" 
                                                class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors" 
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['nombre'])); ?>')" 
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

<!-- Modal Producto -->
<div id="productModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h2 id="modalTitle" class="text-2xl font-bold">Nuevo Producto</h2>
            <button onclick="closeModal()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="productForm" class="p-6 space-y-6" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="id" id="productId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-2">Nombre del producto *</label>
                    <input type="text" name="nombre" id="productNombre" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Categoría *</label>
                    <select name="id_categoria" id="productCategoria" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Precio *</label>
                    <input type="number" name="precio" id="productPrecio" step="0.01" min="0.01" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold mb-2">Stock *</label>
                    <input type="number" name="stock" id="productStock" min="0" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-2">Descripción</label>
                    <textarea name="descripcion" id="productDescripcion" rows="3"
                              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-2">Imagen Principal</label>
                    <div id="imagePreviewContainer" class="hidden mb-4">
                        <div class="relative inline-block">
                            <img id="imagePreview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-slate-300 dark:border-slate-600">
                            <button type="button" onclick="removeImage(1)"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <input type="file" name="imagen" id="productImagen" accept="image/*"
                           onchange="previewImageN(this, 1)"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-100 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Imagen 2 (opcional)</label>
                    <div id="imagePreviewContainer2" class="hidden mb-4">
                        <div class="relative inline-block">
                            <img id="imagePreview2" src="" alt="Preview 2" class="w-32 h-32 object-cover rounded-lg border border-slate-300 dark:border-slate-600">
                            <button type="button" onclick="removeImage(2)"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <input type="file" name="imagen_2" id="productImagen2" accept="image/*"
                           onchange="previewImageN(this, 2)"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-100 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Imagen 3 (opcional)</label>
                    <div id="imagePreviewContainer3" class="hidden mb-4">
                        <div class="relative inline-block">
                            <img id="imagePreview3" src="" alt="Preview 3" class="w-32 h-32 object-cover rounded-lg border border-slate-300 dark:border-slate-600">
                            <button type="button" onclick="removeImage(3)"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <input type="file" name="imagen_3" id="productImagen3" accept="image/*"
                           onchange="previewImageN(this, 3)"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-100 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400">
                </div>

                <div class="md:col-span-2">
                    <p class="text-xs text-slate-500">Formatos: JPG, PNG, GIF, WebP. Máximo 5MB por imagen.</p>
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 px-6 py-3 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors font-semibold">
                    Cancelar
                </button>
                <button type="submit" id="submitBtn"
                        class="flex-1 px-6 py-3 bg-blue-800 hover:bg-blue-900 text-white rounded-lg transition-colors font-semibold">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const API_URL = '<?php echo BASE_URL; ?>/api/products';
const CSRF_TOKEN = '<?php echo $csrf_token; ?>';
let currentImageUrls = { 1: null, 2: null, 3: null };

function openModal(isEdit = false) {
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
    document.getElementById('modalTitle').textContent = isEdit ? 'Editar Producto' : 'Nuevo Producto';

    if (!isEdit) {
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('productImagen').value = '';
        document.getElementById('productImagen2').value = '';
        document.getElementById('productImagen3').value = '';
        for (let i = 1; i <= 3; i++) {
            const suffix = i === 1 ? '' : i;
            document.getElementById('imagePreviewContainer' + suffix).classList.add('hidden');
            currentImageUrls[i] = null;
        }
    }
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
    document.getElementById('productModal').classList.remove('flex');
}

function previewImageN(input, n) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const suffix = n === 1 ? '' : n;
        reader.onload = function(e) {
            document.getElementById('imagePreview' + suffix).src = e.target.result;
            document.getElementById('imagePreviewContainer' + suffix).classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(n) {
    const suffix = n === 1 ? '' : n;
    const inputId = n === 1 ? 'productImagen' : 'productImagen' + n;
    document.getElementById(inputId).value = '';
    document.getElementById('imagePreviewContainer' + suffix).classList.add('hidden');
    currentImageUrls[n] = null;
}

async function editProduct(id) {
    try {
        // Limpiar file inputs antes de cargar datos
        document.getElementById('productImagen').value = '';
        document.getElementById('productImagen2').value = '';
        document.getElementById('productImagen3').value = '';

        const response = await fetch(`${API_URL}?action=get&id=${id}`);
        const data = await response.json();

        if (data.success) {
            const product = data.data;
            document.getElementById('productId').value = product.id;
            document.getElementById('productNombre').value = product.nombre;
            document.getElementById('productDescripcion').value = product.descripcion || '';
            document.getElementById('productCategoria').value = product.id_categoria;
            document.getElementById('productPrecio').value = product.precio;
            document.getElementById('productStock').value = product.stock;

            // Imagen principal
            if (product.imagen_url) {
                document.getElementById('imagePreview').src = product.imagen_url;
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
                currentImageUrls[1] = product.imagen_url;
            } else {
                document.getElementById('imagePreviewContainer').classList.add('hidden');
                currentImageUrls[1] = null;
            }

            // Imagen 2
            if (product.imagen_url_2) {
                document.getElementById('imagePreview2').src = product.imagen_url_2;
                document.getElementById('imagePreviewContainer2').classList.remove('hidden');
                currentImageUrls[2] = product.imagen_url_2;
            } else {
                document.getElementById('imagePreviewContainer2').classList.add('hidden');
                currentImageUrls[2] = null;
            }

            // Imagen 3
            if (product.imagen_url_3) {
                document.getElementById('imagePreview3').src = product.imagen_url_3;
                document.getElementById('imagePreviewContainer3').classList.remove('hidden');
                currentImageUrls[3] = product.imagen_url_3;
            } else {
                document.getElementById('imagePreviewContainer3').classList.add('hidden');
                currentImageUrls[3] = null;
            }

            openModal(true);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar el producto');
    }
}

async function deleteProduct(id, nombre) {
    if (!confirm(`¿Estás seguro de eliminar el producto "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar el producto');
    }
}

document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
    
    const formData = new FormData(this);
    const productId = document.getElementById('productId').value;
    formData.append('action', productId ? 'update' : 'create');
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            let msg = data.message;
            if (data.debug) {
                msg += '\n\n--- DEBUG ---';
                msg += '\nImagen 1: ' + (data.debug.imagen_url || 'null');
                msg += '\nImagen 2: ' + (data.debug.imagen_url_2 || 'null');
                msg += '\nImagen 3: ' + (data.debug.imagen_url_3 || 'null');
                msg += '\nArchivos recibidos: ' + JSON.stringify(data.debug.files_received);
                msg += '\nImagen 2 error: ' + data.debug.imagen_2_error;
            }
            alert(msg);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar el producto');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar';
    }
});

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// Cerrar modal al hacer clic fuera
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>