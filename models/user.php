<?php
/**
 * Modelo User
 * Maneja todas las operaciones relacionadas con usuarios
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'usuarios';

    // Propiedades del usuario
    public $id;
    public $nombre;
    public $apellido;
    public $correo;
    public $contrasena;
    public $rol;
    public $eliminado;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Registrar nuevo usuario
    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  SET nombre = :nombre, 
                      apellido = :apellido, 
                      correo = :correo, 
                      contrasena = :contrasena,
                      rol = 'usuario'";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        $this->correo = htmlspecialchars(strip_tags($this->correo));

        // Hash de la contraseña
        $hashed_password = password_hash($this->contrasena, PASSWORD_BCRYPT);

        // Bind de parámetros
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':contrasena', $hashed_password);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login de usuario
    public function login() {
        $query = "SELECT id, nombre, apellido, correo, contrasena, rol 
                  FROM " . $this->table . " 
                  WHERE correo = :correo AND eliminado = 0 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar contraseña
            if (password_verify($this->contrasena, $row['contrasena'])) {
                $this->id = $row['id'];
                $this->nombre = $row['nombre'];
                $this->apellido = $row['apellido'];
                $this->correo = $row['correo'];
                $this->rol = $row['rol'];
                return true;
            }
        }
        return false;
    }

    // Verificar si el correo ya existe
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE correo = :correo AND eliminado = 0 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Obtener usuario por ID
    public function getById($id) {
        $query = "SELECT id, nombre, apellido, correo, rol, fecha_registro 
                  FROM " . $this->table . " 
                  WHERE id = :id AND eliminado = 0 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Actualizar perfil de usuario
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre, 
                      apellido = :apellido
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Soft delete de usuario
    public function delete($deleted_by) {
        $query = "UPDATE " . $this->table . " 
                  SET eliminado = 1, 
                      fecha_eliminacion = NOW(), 
                      eliminado_por = :eliminado_por 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':eliminado_por', $deleted_by);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Obtener todos los usuarios (para admin)
    public function getAll() {
        $query = "SELECT id, nombre, apellido, correo, rol, fecha_registro 
                  FROM " . $this->table . " 
                  WHERE eliminado = 0 
                  ORDER BY fecha_registro DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generar código de verificación para recuperar contraseña
    public function generateVerificationCode() {
        $code = sprintf("%06d", mt_rand(1, 999999));
        $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $query = "UPDATE " . $this->table . " 
                  SET codigo_verificacion = :code, 
                      codigo_expiracion = :expiration 
                  WHERE correo = :correo";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':expiration', $expiration);
        $stmt->bindParam(':correo', $this->correo);

        if ($stmt->execute()) {
            return $code;
        }
        return false;
    }

    // Verificar código de recuperación
    public function verifyCode($code) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE correo = :correo 
                  AND codigo_verificacion = :code 
                  AND codigo_expiracion > NOW() 
                  AND eliminado = 0 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $this->correo);
        $stmt->bindParam(':code', $code);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Actualizar contraseña
    public function updatePassword() {
        $query = "UPDATE " . $this->table . " 
                  SET contrasena = :contrasena, 
                      codigo_verificacion = NULL, 
                      codigo_expiracion = NULL 
                  WHERE correo = :correo";

        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($this->contrasena, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':contrasena', $hashed_password);
        $stmt->bindParam(':correo', $this->correo);

        return $stmt->execute();
    }
}
?>