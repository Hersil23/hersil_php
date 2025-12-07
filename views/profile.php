<?php
// Incluir header desde views/layouts
require_once __DIR__ . '/layouts/header.php';

// Configuración y base de datos
require_once __DIR__ . '/../config/config.php';   // Sesiones, funciones, entorno
require_once __DIR__ . '/../config/database.php'; // Clase Database

// Verificar sesión
if (!isLoggedIn()) {
    echo "<div class='container mx-auto px-4 py-8'>
            <p class='text-red-600 dark:text-red-400'>Debes iniciar sesión para ver tu perfil.</p>
          </div>";
    require_once __DIR__ . '/layouts/footer.php';
    exit;
}

// Conexión a la base de datos
$db = new Database();
$conn = $db->getConnection();

// Obtener ID del usuario desde la sesión
$user_id = $_SESSION['user_id'];

// Consulta segura con PDO
$stmt = $conn->prepare("SELECT nombre, apellido, correo, rol FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">Perfil de Usuario</h1>

    <?php if ($user): ?>
        <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6">
            <ul class="space-y-2 text-slate-700 dark:text-slate-300">
                <li><strong>Nombre:</strong> <?php echo sanitize($user['nombre']); ?></li>
                <li><strong>Apellido:</strong> <?php echo sanitize($user['apellido']); ?></li>
                <li><strong>Email:</strong> <?php echo sanitize($user['correo']); ?></li>
                <li><strong>Rol:</strong> <?php echo sanitize($user['rol']); ?></li>
            </ul>
        </div>
    <?php else: ?>
        <p class="text-slate-600 dark:text-slate-400">No se encontraron datos del usuario.</p>
    <?php endif; ?>
</main>

<?php
// Incluir footer desde views/layouts
require_once __DIR__ . '/layouts/footer.php';
?>