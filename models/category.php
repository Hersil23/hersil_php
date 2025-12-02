<?php
/**
 * Modelo Category
 * Maneja todas las operaciones relacionadas con categorías
 */

require_once __DIR__ . '/../config/database.php';

class Category {
    private $conn;
    private $table = 'categorias';

    // Propiedades de la categoría
    public $id;
    public $id_usuario;
    public $nombre;
    public $eliminado;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Crear nueva categoría
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET id_usuario = :id_usuario,
                      nombre = :nombre";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));

        // Bind de parámetros
        $stmt->bindParam(':id_usuario', $this->id_usuario);
        $stmt->bindParam(':nombre', $this->nombre);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Obtener todas las categorías (no eliminadas)
    public function getAll() {
        $query = "SELECT c.*, u.nombre as usuario_nombre,
                  (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id AND p.eliminado = 0) as total_productos
                  FROM " . $this->table . " c
                  LEFT JOIN usuarios u ON c.id_usuario = u.id
                  WHERE c.eliminado = 0
                  ORDER BY c.fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener categoría por ID
    public function getById($id) {
        $query = "SELECT c.*, u.nombre as usuario_nombre,
                  (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id AND p.eliminado = 0) as total_productos
                  FROM " . $this->table . " c
                  LEFT JOIN usuarios u ON c.id_usuario = u.id
                  WHERE c.id = :id AND c.eliminado = 0
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Actualizar categoría
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));

        // Bind de parámetros
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Soft delete de categoría
    public function delete($deleted_by) {
        // Verificar si tiene productos asociados
        $query_check = "SELECT COUNT(*) as total FROM productos 
                        WHERE id_categoria = :id AND eliminado = 0";
        
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id', $this->id);
        $stmt_check->execute();
        
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($row['total'] > 0) {
            return false; // No se puede eliminar si tiene productos
        }

        // Si no tiene productos, proceder con el soft delete
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

    // Verificar si el nombre de categoría ya existe
    public function nameExists() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE nombre = :nombre AND eliminado = 0";
        
        // Si estamos actualizando, excluir la categoría actual
        if (isset($this->id)) {
            $query .= " AND id != :id";
        }
        
        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $this->nombre);
        
        if (isset($this->id)) {
            $stmt->bindParam(':id', $this->id);
        }
        
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Obtener categorías activas (con productos)
    public function getActiveCategories() {
        $query = "SELECT DISTINCT c.*
                  FROM " . $this->table . " c
                  INNER JOIN productos p ON c.id = p.id_categoria
                  WHERE c.eliminado = 0 AND p.eliminado = 0
                  ORDER BY c.nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar productos por categoría
    public function getProductCount($categoria_id) {
        $query = "SELECT COUNT(*) as total 
                  FROM productos 
                  WHERE id_categoria = :categoria_id AND eliminado = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoria_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>