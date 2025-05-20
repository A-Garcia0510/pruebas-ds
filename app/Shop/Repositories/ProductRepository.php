<?php
// src/Shop/Repositories/ProductRepository.php
namespace App\Shop\Repositories;

use App\Core\Database\DatabaseInterface;
use App\Shop\Interfaces\ProductRepositoryInterface;
use App\Shop\Models\Product;
use App\Shop\Exceptions\ProductNotFoundException;

class ProductRepository implements ProductRepositoryInterface
{
    private $db;
    
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }
    
    public function findById(int $id): ?Product
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Producto WHERE producto_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $data = $result->fetch_assoc();
        
        return new Product(
            $data['producto_ID'],
            $data['nombre_producto'],
            $data['descripcion'],
            $data['precio'],
            $data['cantidad'],
            $data['categoria']
        );
    }
    
    public function findAll(int $limit = null): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Producto";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $result = $conn->query($sql);
        
        $products = [];
        while ($data = $result->fetch_assoc()) {
            $products[] = new Product(
                $data['producto_ID'],
                $data['nombre_producto'],
                $data['descripcion'],
                $data['precio'],
                $data['cantidad'],
                $data['categoria']
            );
        }
        
        return $products;
    }
    
    public function findByCategory(string $category): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Producto WHERE categoria = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($data = $result->fetch_assoc()) {
            $products[] = new Product(
                $data['producto_ID'],
                $data['nombre_producto'],
                $data['descripcion'],
                $data['precio'],
                $data['cantidad'],
                $data['categoria']
            );
        }
        
        return $products;
    }
    
    public function getAllCategories(): array
    {
        $conn = $this->db->getConnection();
        $result = $conn->query("SELECT DISTINCT categoria FROM Producto");
        
        $categories = [];
        while ($data = $result->fetch_assoc()) {
            $categories[] = $data['categoria'];
        }
        
        return $categories;
    }
    
    public function updateStock(int $id, int $quantity): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE Producto SET cantidad = cantidad - ? WHERE producto_ID = ? AND cantidad >= ?");
        $stmt->bind_param("iii", $quantity, $id, $quantity);
        
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
    
    public function save(Product $product): bool
    {
        $conn = $this->db->getConnection();
        
        if ($product->getId()) {
            // Actualizar producto existente
            $stmt = $conn->prepare("UPDATE Producto SET nombre_producto = ?, descripcion = ?, precio = ?, cantidad = ?, categoria = ? WHERE producto_ID = ?");
            $name = $product->getName();
            $description = $product->getDescription();
            $price = $product->getPrice();
            $stock = $product->getStock();
            $category = $product->getCategory();
            $id = $product->getId();
            
            $stmt->bind_param("ssddsi", $name, $description, $price, $stock, $category, $id);
            return $stmt->execute();
        } else {
            // Crear nuevo producto
            $stmt = $conn->prepare("INSERT INTO Producto (nombre_producto, descripcion, precio, cantidad, categoria) VALUES (?, ?, ?, ?, ?)");
            $name = $product->getName();
            $description = $product->getDescription();
            $price = $product->getPrice();
            $stock = $product->getStock();
            $category = $product->getCategory();
            
            $stmt->bind_param("ssdis", $name, $description, $price, $stock, $category);
            
            if ($stmt->execute()) {
                $product->setId($conn->insert_id);
                return true;
            }
            
            return false;
        }
    }
}