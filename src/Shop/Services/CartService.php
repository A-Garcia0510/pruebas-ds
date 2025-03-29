<?php
// src/Shop/Services/CartService.php
namespace App\Shop\Services;

use App\Core\Database\DatabaseInterface;
use App\Shop\Interfaces\CartInterface;
use App\Shop\Interfaces\ProductRepositoryInterface;
use App\Shop\Models\CartItem;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Shop\Exceptions\InsufficientStockException;

class CartService implements CartInterface
{
    private $db;
    private $productRepository;
    
    public function __construct(
        DatabaseInterface $db,
        ProductRepositoryInterface $productRepository
    ) {
        $this->db = $db;
        $this->productRepository = $productRepository;
    }
    
    public function addItem(string $userId, int $productId, int $quantity): bool
    {
        $conn = $this->db->getConnection();
        
        // Verificar si el producto existe y tiene stock suficiente
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new ProductNotFoundException("El producto no existe");
        }
        
        if (!$product->hasStock($quantity)) {
            throw new InsufficientStockException("No hay stock suficiente");
        }
        
        // Verificar si el producto ya está en el carrito
        $stmt = $conn->prepare("SELECT * FROM Carrito WHERE usuario_email = ? AND producto_ID = ?");
        $stmt->bind_param("si", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar cantidad
            $stmt = $conn->prepare("UPDATE Carrito SET cantidad = cantidad + ? WHERE usuario_email = ? AND producto_ID = ?");
            $stmt->bind_param("isi", $quantity, $userId, $productId);
        } else {
            // Insertar nuevo item
            $stmt = $conn->prepare("INSERT INTO Carrito (usuario_email, producto_ID, cantidad) VALUES (?, ?, ?)");
            $stmt->bind_param("sii", $userId, $productId, $quantity);
        }
        
        return $stmt->execute();
    }
    
    public function removeItem(string $userId, int $productId): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("DELETE FROM Carrito WHERE usuario_email = ? AND producto_ID = ?");
        $stmt->bind_param("si", $userId, $productId);
        
        return $stmt->execute();
    }
    
    public function getItems(string $userId): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT c.*, p.nombre_producto, p.precio 
            FROM Carrito c
            JOIN Producto p ON c.producto_ID = p.producto_ID
            WHERE c.usuario_email = ?
        ");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($data = $result->fetch_assoc()) {
            $items[] = new CartItem(
                $data['usuario_email'],
                $data['producto_ID'],
                $data['cantidad'],
                $data['nombre_producto'],
                $data['precio']
            );
        }
        
        return $items;
    }
    
    public function clear(string $userId): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("DELETE FROM Carrito WHERE usuario_email = ?");
        $stmt->bind_param("s", $userId);
        
        return $stmt->execute();
    }
    
    public function getTotal(string $userId): float
    {
        $items = $this->getItems($userId);
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item->getSubtotal();
        }
        
        return $total;
    }
}