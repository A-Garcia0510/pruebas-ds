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
        
        // Primero obtenemos el usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userId); // $userId aquí es el correo electrónico
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID']; // ID numérico del usuario
        
        // Verificar si el producto existe y tiene stock suficiente
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new ProductNotFoundException("El producto no existe");
        }
        
        if (!$product->hasStock($quantity)) {
            throw new InsufficientStockException(
                "No hay stock suficiente", 
                400, 
                null, 
                $productId, 
                $quantity, 
                $product->getStock()
            );
        }
        
        // Verificar si el producto ya está en el carro
        $stmt = $conn->prepare("SELECT * FROM Carro WHERE usuario_ID = ? AND producto_ID = ?");
        $stmt->bind_param("ii", $userIdInt, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar cantidad
            $stmt = $conn->prepare("UPDATE Carro SET cantidad = cantidad + ? WHERE usuario_ID = ? AND producto_ID = ?");
            $stmt->bind_param("iii", $quantity, $userIdInt, $productId);
        } else {
            // Insertar nuevo item
            $stmt = $conn->prepare("INSERT INTO Carro (usuario_ID, producto_ID, cantidad) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userIdInt, $productId, $quantity);
        }
        
        return $stmt->execute();
    }
    
    public function removeItem(string $userId, int $productId): bool
    {
        $conn = $this->db->getConnection();
        
        // Obtener usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userId);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID'];
        
        $stmt = $conn->prepare("DELETE FROM Carro WHERE usuario_ID = ? AND producto_ID = ?");
        $stmt->bind_param("ii", $userIdInt, $productId);
        
        return $stmt->execute();
    }
    
    public function getItems(string $userId): array
    {
        $conn = $this->db->getConnection();
        
        // Obtener usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userId);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID'];
        
        $stmt = $conn->prepare("
            SELECT c.*, p.nombre_producto, p.precio 
            FROM Carro c
            JOIN Producto p ON c.producto_ID = p.producto_ID
            WHERE c.usuario_ID = ?
        ");
        $stmt->bind_param("i", $userIdInt);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($data = $result->fetch_assoc()) {
            $items[] = new CartItem(
                $userId, // Mantenemos el correo como identificador en el objeto CartItem
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
        
        // Obtener usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userId);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID'];
        
        $stmt = $conn->prepare("DELETE FROM Carro WHERE usuario_ID = ?");
        $stmt->bind_param("i", $userIdInt);
        
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