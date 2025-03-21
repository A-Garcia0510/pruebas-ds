<?php
require_once 'Database.php';
require_once 'User.php';
require_once 'Product.php';

class Cart {
    private $db;
    private $user;
    private $product;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->user = new User();
        $this->product = new Product();
    }
    
    public function getCartItems($correo) {
        $conn = $this->db->getConnection();
        $sql = "SELECT u.usuario_ID, p.producto_ID, p.nombre_producto, p.precio, c.cantidad
                FROM Carro c
                JOIN Usuario u ON c.usuario_ID = u.usuario_ID
                JOIN Producto p ON c.producto_ID = p.producto_ID
                WHERE u.correo = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        
        return $items;
    }
    
    public function addToCart($correo, $productoID, $cantidad) {
        $conn = $this->db->getConnection();
        
        // Obtener el ID del usuario
        $usuarioID = $this->getUserIdByEmail($correo);
        if (!$usuarioID) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }
        
        // Verificar si el producto ya está en el carrito
        $carroSql = "SELECT cantidad FROM Carro WHERE usuario_ID = ? AND producto_ID = ?";
        $carroStmt = $conn->prepare($carroSql);
        $carroStmt->bind_param("ii", $usuarioID, $productoID);
        $carroStmt->execute();
        $carroResult = $carroStmt->get_result();
        
        if ($carroResult->num_rows > 0) {
            // Actualizar cantidad
            $carroRow = $carroResult->fetch_assoc();
            $nuevaCantidad = $carroRow['cantidad'] + $cantidad;
            $updateSql = "UPDATE Carro SET cantidad = ? WHERE usuario_ID = ? AND producto_ID = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("iii", $nuevaCantidad, $usuarioID, $productoID);
            $success = $updateStmt->execute();
        } else {
            // Insertar nuevo producto
            $insertSql = "INSERT INTO Carro (usuario_ID, producto_ID, cantidad) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iii", $usuarioID, $productoID, $cantidad);
            $success = $insertStmt->execute();
        }
        
        if ($success) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al agregar al carrito.'];
        }
    }
    
    public function removeFromCart($correo, $productoID) {
        $conn = $this->db->getConnection();
        
        // Obtener el ID del usuario
        $usuarioID = $this->getUserIdByEmail($correo);
        if (!$usuarioID) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }
        
        // Eliminar el producto del carrito
        $sql = "DELETE FROM Carro WHERE usuario_ID = ? AND producto_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $usuarioID, $productoID);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el producto del carrito.'];
        }
    }
    
    public function clearCart($correo) {
        $conn = $this->db->getConnection();
        
        // Obtener el ID del usuario
        $usuarioID = $this->getUserIdByEmail($correo);
        if (!$usuarioID) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }
        
        $sql = "DELETE FROM Carro WHERE usuario_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioID);
        
        return $stmt->execute();
    }
    
    public function isCartEmpty($correo) {
        $conn = $this->db->getConnection();
        
        // Obtener el ID del usuario
        $usuarioID = $this->getUserIdByEmail($correo);
        if (!$usuarioID) {
            return true; // Si no hay usuario, consideramos que el carrito está vacío
        }
        
        $sql = "SELECT COUNT(*) AS total FROM Carro WHERE usuario_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] == 0;
    }
    
    private function getUserIdByEmail($correo) {
        $conn = $this->db->getConnection();
        $sql = "SELECT usuario_ID FROM Usuario WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['usuario_ID'];
        }
        
        return null;
    }
}
?>