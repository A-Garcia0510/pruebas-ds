<?php
require_once 'Database.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllProducts() {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Producto";
        $result = $conn->query($sql);
        
        $products = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        return $products;
    }
    
    public function getProductsByCategory($categoria) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Producto WHERE categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        return $products;
    }
    
    public function getAllCategories() {
        $conn = $this->db->getConnection();
        $sql = "SELECT DISTINCT categoria FROM Producto";
        $result = $conn->query($sql);
        
        $categorias = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $categorias[] = $row['categoria'];
            }
        }
        
        return $categorias;
    }
    
    public function updateStock($productoID, $cantidad) {
        $conn = $this->db->getConnection();
        $sql = "UPDATE Producto SET cantidad = cantidad - ? WHERE producto_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cantidad, $productoID);
        return $stmt->execute();
    }
    
    public function getProductById($productoID) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Producto WHERE producto_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productoID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}
?>