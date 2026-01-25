<?php
require_once __DIR__ . '/config/database.php';

$newPassword = 'Miranda01@';
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$database = new Database();
$db = $database->connect();

$stmt = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE correo = ?");
$result = $stmt->execute([$hash, 'herasidesweb@gmail.com']);

if ($result) {
    echo "Contraseña actualizada correctamente.<br>";
    echo "Hash generado: " . $hash;
} else {
    echo "Error al actualizar la contraseña.";
}
?>