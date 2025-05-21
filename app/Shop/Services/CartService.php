<?php
// src/Shop/Services/CartService.php
namespace App\Shop\Services;

use App\Core\Database\DatabaseInterface;
use App\Shop\Interfaces\CartInterface;
use App\Shop\Interfaces\ProductRepositoryInterface;
use App\Shop\Models\CartItem;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Shop\Exceptions\InsufficientStockException;
use PDO;
use Exception;

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
    
    public function addItem(string $userEmail, int $productId, int $quantity, bool $actualizar = false): bool
    {
        try {
            // Verificar stock disponible
            $product = $this->productRepository->findById($productId);
            if (!$product) {
                error_log("CartService::addItem - Producto no encontrado: " . $productId);
                return false;
            }

            // Verificar stock usando el método getStock() del objeto Product
            if ($product->getStock() < $quantity) {
                error_log("CartService::addItem - Stock insuficiente para producto: " . $productId . ". Stock disponible: " . $product->getStock());
                return false;
            }

            // Verificar si el producto ya está en el carrito
            $cartItem = $this->getCartItem($userEmail, $productId);
            
            if ($cartItem) {
                // Si el producto ya existe y estamos actualizando, simplemente actualizamos la cantidad
                if ($actualizar) {
                    return $this->updateItemQuantity($userEmail, $productId, $quantity);
                }
                // Si no estamos actualizando, sumamos la cantidad nueva a la existente
                $quantity += $cartItem['cantidad'];
            }

            // Obtener usuario_ID a partir del correo
            $conn = $this->db->getConnection();
            $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
            $stmtUser->bind_param("s", $userEmail);
            $stmtUser->execute();
            $userResult = $stmtUser->get_result();
            
            if ($userResult->num_rows === 0) {
                error_log("CartService::addItem - Usuario no encontrado: " . $userEmail);
                return false;
            }
            
            $userData = $userResult->fetch_assoc();
            $userIdInt = $userData['usuario_ID'];

            // Si el producto no existe en el carrito, lo agregamos
            if (!$cartItem) {
                $stmt = $conn->prepare("INSERT INTO Carro (usuario_ID, producto_ID, cantidad) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $userIdInt, $productId, $quantity);
                $result = $stmt->execute();
                
                if (!$result) {
                    error_log("CartService::addItem - Error al insertar en carrito: " . $stmt->error);
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log("Error en CartService::addItem: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateItemQuantity(string $userEmail, int $productId, int $quantity): bool
    {
        try {
            $conn = $this->db->getConnection();
            
            // Obtener usuario_ID a partir del correo
            $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
            $stmtUser->bind_param("s", $userEmail);
            $stmtUser->execute();
            $userResult = $stmtUser->get_result();
            
            if ($userResult->num_rows === 0) {
                error_log("CartService::updateItemQuantity - Usuario no encontrado: " . $userEmail);
                return false;
            }
            
            $userData = $userResult->fetch_assoc();
            $userIdInt = $userData['usuario_ID'];

            $stmt = $conn->prepare("UPDATE Carro SET cantidad = ? WHERE usuario_ID = ? AND producto_ID = ?");
            $stmt->bind_param("iii", $quantity, $userIdInt, $productId);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("CartService::updateItemQuantity - Error al actualizar cantidad: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error en CartService::updateItemQuantity: " . $e->getMessage());
            return false;
        }
    }
    
    private function getCartItem(string $userEmail, int $productId): ?array
    {
        try {
            $conn = $this->db->getConnection();
            
            // Obtener usuario_ID a partir del correo
            $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
            $stmtUser->bind_param("s", $userEmail);
            $stmtUser->execute();
            $userResult = $stmtUser->get_result();
            
            if ($userResult->num_rows === 0) {
                error_log("CartService::getCartItem - Usuario no encontrado: " . $userEmail);
                return null;
            }
            
            $userData = $userResult->fetch_assoc();
            $userIdInt = $userData['usuario_ID'];

            $stmt = $conn->prepare("SELECT * FROM Carro WHERE usuario_ID = ? AND producto_ID = ?");
            $stmt->bind_param("ii", $userIdInt, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc() ?: null;
        } catch (\Exception $e) {
            error_log("Error en CartService::getCartItem: " . $e->getMessage());
            return null;
        }
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

    /**
     * Obtiene un item específico del carrito
     * 
     * @param string $userEmail Email del usuario
     * @param int $productId ID del producto
     * @return CartItem|null
     */
    public function getItem($userEmail, $productId)
    {
        $conn = $this->db->getConnection();
        
        // Obtener usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userEmail);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            return null;
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID'];
        
        $stmt = $conn->prepare("
            SELECT c.*, p.nombre_producto, p.precio 
            FROM Carro c 
            JOIN Producto p ON c.producto_ID = p.producto_ID 
            WHERE c.usuario_ID = ? AND c.producto_ID = ?
        ");
        $stmt->bind_param("ii", $userIdInt, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $data = $result->fetch_assoc();
        return new CartItem(
            $userEmail,
            $data['producto_ID'],
            $data['cantidad'],
            $data['nombre_producto'],
            $data['precio']
        );
    }

    /**
     * Actualiza la cantidad de un producto en el carrito
     * 
     * @param string $userEmail Email del usuario
     * @param int $productId ID del producto
     * @param int $quantity Nueva cantidad
     * @return bool
     */
    public function updateQuantity($userEmail, $productId, $quantity)
    {
        $conn = $this->db->getConnection();
        
        // Verificar stock disponible
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new ProductNotFoundException("Producto no encontrado");
        }
        
        if (!$product->hasStock($quantity)) {
            throw new InsufficientStockException(
                "Stock insuficiente. Solo hay " . $product->getStock() . " unidades disponibles.",
                400,
                null,
                $productId,
                $quantity,
                $product->getStock()
            );
        }
        
        // Obtener usuario_ID a partir del correo
        $stmtUser = $conn->prepare("SELECT usuario_ID FROM Usuario WHERE correo = ?");
        $stmtUser->bind_param("s", $userEmail);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();
        
        if ($userResult->num_rows === 0) {
            throw new \Exception("Usuario no encontrado");
        }
        
        $userData = $userResult->fetch_assoc();
        $userIdInt = $userData['usuario_ID'];
        
        $stmt = $conn->prepare("
            UPDATE Carro 
            SET cantidad = ? 
            WHERE usuario_ID = ? AND producto_ID = ?
        ");
        $stmt->bind_param("iii", $quantity, $userIdInt, $productId);
        
        return $stmt->execute();
    }
}