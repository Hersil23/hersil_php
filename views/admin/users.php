<?php
$page_title = "Gestión de Usuarios";
require_once __DIR__ . '/../layouts/header.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = "No tienes permiso para acceder a esta página";
    redirect('/public/');
    exit;
}

require_once __DIR__ . '/../../models/user.php';
$userModel = new User();

$busqueda = isset($_GET['busqueda']) ? sanitize($_GET['busqueda']) : '';
$filtro_rol = isset($_GET['rol']) ? sanitize($_GET['rol']) : '';

if ($busqueda) {
    $users = $userModel->search($busqueda);
} else {
    $users = $userModel->getAllUsers();
}

if ($filtro_rol && $filtro_rol !== 'todos') {
    $users = array_filter($users, function($user) use ($filtro_rol) {
        return $user['rol'] === $filtro_rol;
    });
}
?>

<div class="bg-slate-50 dark:bg-slate-900/50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 flex items-center">
                <i class="fas fa-users-cog mr-3 text-blue-600 dark:text-blue-400"></i>
                Gestión de Usuarios
            </h1>
            <p class="text-slate-600 dark:text-slate-400">Administra todos los usuarios del sistema</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-6 mb-6 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                
                <form method="GET" class="flex-1 w-full md:w-auto">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="busqueda" 
                            placeholder="Buscar por nombre, apellido o correo..."
                            value="<?php echo htmlspecialchars($busqueda); ?>"
                            class="w-full px-4 py-3 pl-10 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-900 dark:text-slate-100"
                        >
                        <i class="fas fa-search absolute left-3 top-4 text-slate-400"></i>
                    </div>
                </form>

                <div class="flex gap-3 w-full md:w-auto">
                    <select name="rol" onchange="window.location.href='?rol=' + this.value + '<?php echo $busqueda ? '&busqueda=' . urlencode($busqueda) : ''; ?>'" 
                            class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-slate-100">
                        <option value="todos" <?php echo $filtro_rol === 'todos' || !$filtro_rol ? 'selected' : ''; ?>>Todos los roles</option>
                        <option value="administrador" <?php echo $filtro_rol === 'administrador' ? 'selected' : ''; ?>>Administradores</option>
                        <option value="usuario" <?php echo $filtro_rol === 'usuario' ? 'selected' : ''; ?>>Usuarios</option>
                    </select>
                    
                    <?php if ($busqueda || $filtro_rol): ?>
                    <a href="<?php echo BASE_URL; ?>/public/admin/usuarios" 
                       class="px-4 py-3 bg-slate-200 dark:bg-slate-700 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden border border-slate-200 dark:border-slate-700">
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-100 dark:bg-slate-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Usuario</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Correo</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Rol</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold">Registro</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <i class="fas fa-user-slash text-4xl mb-3 block"></i>
                                No se encontraron usuarios
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono">#<?php echo $user['id']; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($user['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['correo']); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $user['rol'] === 'administrador' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'; ?>">
                                    <i class="fas <?php echo $user['rol'] === 'administrador' ? 'fa-crown' : 'fa-user'; ?> mr-1"></i>
                                    <?php echo ucfirst($user['rol']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                <?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="viewUser(<?php echo $user['id']; ?>)" 
                                            class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors" 
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editUser(<?php echo $user['id']; ?>)" 
                                            class="p-2 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors" 
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nombre']); ?>')" 
                                            class="p-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors" 
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-700/50 border-t border-slate-200 dark:border-slate-600">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Total de usuarios: <strong><?php echo count($users); ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const API_URL = '<?php echo BASE_URL; ?>/api/users';

function viewUser(id) {
    alert('Ver detalles del usuario #' + id + '\n(Funcionalidad pendiente)');
}

function editUser(id) {
    alert('Editar usuario #' + id + '\n(Funcionalidad pendiente)');
}

async function deleteUser(id, nombre) {
    if (!confirm('¿Estás seguro de eliminar al usuario "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
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
        alert('Error al eliminar el usuario');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>