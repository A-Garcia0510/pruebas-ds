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
        error_log("CartService::addItem() - Iniciando con userId: " . $userId . ", productId: " . $productId . ", quantity: " . $quantity);
        
        $conn = $this->db->getConnection();
        
        // Primero obtenemos el usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userId); // $userId aquí es el correo electrónico
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            error_log("CartService::addItem() - Usuario no encontrado: " . $userId);
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID']; // ID numérico del usuario
        error_log("CartService::addItem() - usuario_ID encontrado: " . $userIdInt);
        
        // Verificar si el producto existe y tiene stock suficiente
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            error_log("CartService::addItem() - Producto no encontrado: " . $productId);
            throw new ProductNotFoundException("El producto no existe");
        }
        
        if (!$product->hasStock($quantity)) {
            error_log("CartService::addItem() - Stock insuficiente para producto: " . $productId);
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
            error_log("CartService::addItem() - Actualizando cantidad existente");
            // Actualizar cantidad
            $stmt = $conn->prepare("UPDATE Carro SET cantidad = cantidad + ? WHERE usuario_ID = ? AND producto_ID = ?");
            $stmt->bind_param("iii", $quantity, $userIdInt, $productId);
        } else {
            error_log("CartService::addItem() - Insertando nuevo item");
            // Insertar nuevo item
            $stmt = $conn->prepare("INSERT INTO Carro (usuario_ID, producto_ID, cantidad) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userIdInt, $productId, $quantity);
        }
        
        $success = $stmt->execute();
        error_log("CartService::addItem() - Operación completada con éxito: " . ($success ? "Sí" : "No"));
        
        return $success;
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
        error_log("CartService::getItems() - Iniciando con userId: " . $userId);
        
        $conn = $this->db->getConnection();
        
        // Obtener usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userId);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            error_log("CartService::getItems() - Usuario no encontrado: " . $userId);
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID'];
        error_log("CartService::getItems() - usuario_ID encontrado: " . $userIdInt);
        
        $stmt = $conn->prepare("
            SELECT c.*, p.nombre_producto, p.precio 
            FROM Carro c
            JOIN Producto p ON c.producto_ID = p.producto_ID
            WHERE c.usuario_ID = ?
        ");
        $stmt->bind_param("i", $userIdInt);
        $stmt->execute();
        $result = $stmt->get_result();
        
        error_log("CartService::getItems() - Número de items encontrados: " . $result->num_rows);
        
        $items = [];
        while ($data = $result->fetch_assoc()) {
            error_log("CartService::getItems() - Procesando item: " . json_encode($data));
            $items[] = new CartItem(
                $userId, // Mantenemos el correo como identificador en el objeto CartItem
                $data['producto_ID'],
                $data['cantidad'],
                $data['nombre_producto'],
                $data['precio']
            );
        }
        
        error_log("CartService::getItems() - Total de items procesados: " . count($items));
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