<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

try {
    $newPassword = 'Miranda01@';
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
    $result = $stmt->execute([$hash, 'herasidesweb@gmail.com']);

    if ($result) {
        echo "Contraseña actualizada correctamente.<br>";
        echo "Hash generado: " . $hash;
    } else {
        echo "Error al actualizar la contraseña.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>