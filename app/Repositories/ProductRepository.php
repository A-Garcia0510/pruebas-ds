<?php
namespace Repositories;

use app\Interfaces\ProductInterface;
use app\Config\Database;

class ProductRepository implements ProductInterface {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database;
    }

    public function getAllProducts(): array {
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

    // Implementar los demás métodos de la interfaz
}