<?php
namespace App\Repositories;

use App\Config\Database;
use App\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface {
    private Database $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function findAll(): array {
        $conn = $this->database->getConnection();
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

    public function findByCategory(string $category): array {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Producto WHERE categoria = ?");
        $stmt->bind_param("s", $category);
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

    public function findAllCategories(): array {
        $conn = $this->database->getConnection();
        $sql = "SELECT DISTINCT categoria FROM Producto";
        $result = $conn->query($sql);
        
        $categories = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $categories[] = $row['categoria'];
            }
        }
        
        return $categories;
    }

    public function updateStock(int $productId, int $quantity): bool {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare("UPDATE Producto SET cantidad = cantidad - ? WHERE producto_ID = ?");
        $stmt->bind_param("ii", $quantity, $productId);
        return $stmt->execute();
    }

    public function findById(int $productId): ?array {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Producto WHERE producto_ID = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
}