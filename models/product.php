<?php
/**
 * Modelo Product
 * Maneja todas las operaciones relacionadas con productos
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'productos';

    // Propiedades del producto
    public $id;
    public $id_usuario;
    public $id_categoria;
    public $nombre;
    public $descripcion;
    public $stock;
    public $precio;
    public $eliminado;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Crear nuevo producto
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET id_usuario = :id_usuario,
                      id_categoria = :id_categoria,
                      nombre = :nombre,
                      descripcion = :descripcion,
                      stock = :stock,
                      precio = :precio";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));

        // Bind de parámetros
        $stmt->bindParam(':id_usuario', $this->id_usuario);
        $stmt->bindParam(':id_categoria', $this->id_categoria);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':precio', $this->precio);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Obtener todos los productos (no eliminados)
    public function getAll() {
        $query = "SELECT p.*, c.nombre as categoria_nombre, u.nombre as usuario_nombre
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  LEFT JOIN usuarios u ON p.id_usuario = u.id
                  WHERE p.eliminado = 0
                  ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener productos por categoría
    public function getByCategory($categoria_id) {
        $query = "SELECT p.*, c.nombre as categoria_nombre
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  WHERE p.id_categoria = :categoria_id 
                  AND p.eliminado = 0
                  ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoria_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar productos por nombre
    public function search($keyword) {
        $query = "SELECT p.*, c.nombre as categoria_nombre
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  WHERE p.nombre LIKE :keyword 
                  AND p.eliminado = 0
                  ORDER BY p.fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $search_keyword = "%" . $keyword . "%";
        $stmt->bindParam(':keyword', $search_keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener producto por ID
    public function getById($id) {
        $query = "SELECT p.*, c.nombre as categoria_nombre, u.nombre as usuario_nombre
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  LEFT JOIN usuarios u ON p.id_usuario = u.id
                  WHERE p.id = :id AND p.eliminado = 0
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Actualizar producto
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET id_categoria = :id_categoria,
                      nombre = :nombre,
                      descripcion = :descripcion,
                      stock = :stock,
                      precio = :precio
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));

        // Bind de parámetros
        $stmt->bindParam(':id_categoria', $this->id_categoria);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':precio', $this->precio);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Soft delete de producto
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

    // Verificar si hay stock disponible
    public function hasStock($cantidad = 1) {
        $query = "SELECT stock FROM " . $this->table . " 
                  WHERE id = :id AND eliminado = 0 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['stock'] >= $cantidad;
        }
        return false;
    }

    // Obtener productos con stock bajo (menos de 5)
    public function getLowStock() {
        $query = "SELECT p.*, c.nombre as categoria_nombre
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  WHERE p.stock < 5 AND p.eliminado = 0
                  ORDER BY p.stock ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar productos por categoría
    public function countByCategory() {
        $query = "SELECT c.nombre, COUNT(p.id) as total
                  FROM categorias c
                  LEFT JOIN " . $this->table . " p ON c.id = p.id_categoria AND p.eliminado = 0
                  WHERE c.eliminado = 0
                  GROUP BY c.id, c.nombre
                  ORDER BY total DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>