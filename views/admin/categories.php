<?php
$page_title = "Gestión de Categorías";
require_once __DIR__ . '/../layouts/header.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "No tienes permiso para acceder a esta página";
    redirect('/public/');
    exit;
}

require_once __DIR__ . '/../../models/category.php';
$categoryModel = new Category();

$busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';

if ($busqueda) {
    $categories = $categoryModel->search($busqueda);
} else {
    $categories = $categoryModel->getAll();
}

$csrfToken = Security::generateCSRFToken();
?>

<div class="bg-slate-50 dark:bg-slate-900/50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 flex items-center">
                <i class="fas fa-tags mr-3 text-blue-600 dark:text-blue-400"></i>
                Gestión de Categorías
            </h1>
            <p class="text-slate-600 dark:text-slate-400">Administra las categorías de productos</p>
        </div>

        <!-- Barra de acciones -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 mb-6 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                
                <form method="GET" class="flex-1 w-full md:w-auto">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="busqueda" 
                            placeholder="Buscar categorías..."
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            class="w-full px-4 py-3 pl-10 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-slate-100"
                        >
                        <i class="fas fa-search absolute left-3 top-4 text-slate-400"></i>
                    </div>
                </form>

                <div class="flex gap-3 w-full md:w-auto">
                    <button onclick="openCreateModal()" 
                            class="flex-1 md:flex-none px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-semibold">
                        <i class="fas fa-plus mr-2"></i>Nueva Categoría
                    </button>
                    
                    <?php if ($busqueda): ?>
                    <a href="<?php echo BASE_URL; ?>/public/admin/categorias" 
                       class="px-4 py-3 bg-slate-200 dark:bg-slate-700 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Grid de categorías -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($categories)): ?>
                <div class="col-span-full bg-white dark:bg-slate-800 rounded-xl shadow-lg p-12 text-center border border-slate-200 dark:border-slate-700">
                    <i class="fas fa-folder-open text-6xl text-slate-300 dark:text-slate-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">No se encontraron categorías</h3>
                    <p class="text-slate-600 dark:text-slate-400 mb-4">
                        <?php echo $busqueda ? 'Intenta con otros términos de búsqueda' : 'Comienza creando una nueva categoría'; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all" id="category-<?php echo $category['id']; ?>">
                        
                        <!-- Imagen -->
                        <div class="h-40 bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-600 relative overflow-hidden">
                            <?php if (!empty($category['imagen_url'])): ?>
                                <img src="<?php echo htmlspecialchars($category['imagen_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($category['nombre']); ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-image text-4xl text-slate-400 dark:text-slate-500"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge de estado -->
                            <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-semibold <?php echo $category['activo'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                                <?php echo $category['activo'] ? 'Activa' : 'Inactiva'; ?>
                            </span>
                        </div>

                        <div class="p-5">
                            <h3 class="text-xl font-bold mb-2 text-slate-900 dark:text-white">
                                <?php echo htmlspecialchars($category['nombre']); ?>
                            </h3>

                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 line-clamp-2 min-h-[2.5rem]">
                                <?php echo htmlspecialchars($category['descripcion'] ?? 'Sin descripción'); ?>
                            </p>

                            <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400 mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-box"></i>
                                    <span><?php echo $category['total_productos'] ?? 0; ?> productos</span>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex gap-2">
                                <button onclick="openEditModal(<?php echo $category['id']; ?>)" 
                                        class="flex-1 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-sm font-semibold">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                                <button onclick="toggleCategory(<?php echo $category['id']; ?>)" 
                                        class="px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded-lg hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors text-sm"
                                        title="<?php echo $category['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                    <i class="fas fa-<?php echo $category['activo'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                </button>
                                <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['nombre']); ?>')" 
                                        class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors text-sm"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="mt-6 bg-white dark:bg-slate-800 rounded-xl shadow-lg p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Total de categorías: <strong><?php echo count($categories); ?></strong>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Crear/Editar -->
<div id="categoryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-bold">Nueva Categoría</h3>
                <button onclick="closeModal()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <form id="categoryForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="id" id="categoryId">
            
            <div class="p-6 space-y-5">
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Nombre *</label>
                    <input type="text" name="nombre" id="categoryNombre" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nombre de la categoría">
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Descripción</label>
                    <textarea name="descripcion" id="categoryDescripcion" rows="3"
                              class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Descripción opcional"></textarea>
                </div>

                <!-- Imagen -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Imagen</label>
                    
                    <!-- Preview -->
                    <div id="imagePreviewContainer" class="hidden mb-3">
                        <div class="relative inline-block">
                            <img id="imagePreview" src="" class="w-32 h-32 object-cover rounded-lg border">
                            <button type="button" onclick="removeImage()" 
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt text-3xl text-slate-400 mb-2"></i>
                            <p class="text-sm text-slate-500">Clic para subir imagen</p>
                            <p class="text-xs text-slate-400">PNG, JPG, WEBP (máx. 5MB)</p>
                        </div>
                        <input type="file" name="imagen" id="categoryImagen" class="hidden" accept="image/*">
                    </label>
                </div>
            </div>

            <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex gap-3">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 px-6 py-3 bg-slate-200 dark:bg-slate-700 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors font-semibold">
                    Cancelar
                </button>
                <button type="submit" id="submitBtn"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-semibold">
                    <span id="submitText">Guardar</span>
                    <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?php echo $csrfToken; ?>';
const CONTROLLER_URL = '<?php echo BASE_URL; ?>/controllers/CategoryController.php';

let isEditing = false;
let currentImageUrl = null;

// Abrir modal para crear
function openCreateModal() {
    isEditing = false;
    document.getElementById('modalTitle').textContent = 'Nueva Categoría';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    currentImageUrl = null;
    openModal();
}

// Abrir modal para editar
async function openEditModal(id) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = 'Editar Categoría';
    
    try {
        const response = await fetch(`${CONTROLLER_URL}?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('categoryId').value = data.data.id;
            document.getElementById('categoryNombre').value = data.data.nombre;
            document.getElementById('categoryDescripcion').value = data.data.descripcion || '';
            
            if (data.data.imagen_url) {
                document.getElementById('imagePreview').src = data.data.imagen_url;
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
                currentImageUrl = data.data.imagen_url;
            } else {
                document.getElementById('imagePreviewContainer').classList.add('hidden');
                currentImageUrl = null;
            }
            
            openModal();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error al cargar la categoría', 'error');
    }
}

function openModal() {
    document.getElementById('categoryModal').classList.remove('hidden');
    document.getElementById('categoryModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    document.getElementById('categoryModal').classList.remove('flex');
}

// Preview de imagen
document.getElementById('categoryImagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
});

function removeImage() {
    document.getElementById('categoryImagen').value = '';
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    currentImageUrl = null;
}

// Enviar formulario
document.getElementById('categoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    submitBtn.disabled = true;
    submitText.textContent = 'Guardando...';
    submitSpinner.classList.remove('hidden');
    
    const formData = new FormData(this);
    formData.append('action', isEditing ? 'update' : 'create');
    
    try {
        const response = await fetch(CONTROLLER_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error de conexión', 'error');
    } finally {
        submitBtn.disabled = false;
        submitText.textContent = 'Guardar';
        submitSpinner.classList.add('hidden');
    }
});

// Toggle activo/inactivo
async function toggleCategory(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(CONTROLLER_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error de conexión', 'error');
    }
}

// Eliminar categoría
async function deleteCategory(id, nombre) {
    if (!confirm(`¿Eliminar la categoría "${nombre}"?\n\nSolo se puede eliminar si no tiene productos.`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        formData.append('csrf_token', CSRF_TOKEN);
        
        const response = await fetch(CONTROLLER_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            document.getElementById('category-' + id).remove();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('Error de conexión', 'error');
    }
}

// Notificaciones
function showNotification(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in`;
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>