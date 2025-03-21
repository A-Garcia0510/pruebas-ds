<?php
require_once 'Database.php';
require_once 'Cart.php';
require_once 'Product.php';

class Purchase {
    private $db;
    private $cart;
    private $product;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->cart = new Cart();
        $this->product = new Product();
    }
    
    public function createPurchase($correo) {
        $conn = $this->db->getConnection();
        
        // Verificar si el carrito está vacío
        if ($this->cart->isCartEmpty($correo)) {
            return ['success' => false, 'message' => 'El carrito está vacío.'];
        }
        
        // Obtener el ID del usuario
        $usuarioID = $this->getUserIdByEmail($correo);
        if (!$usuarioID) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Crear la compra
            $sqlCrearCompra = "INSERT INTO Compra (usuario_ID, fecha_compra, total) 
                           SELECT ?, NOW(), SUM(p.precio * c.cantidad) 
                           FROM Carro c 
                           JOIN Producto p ON c.producto_ID = p.producto_ID 
                           WHERE c.usuario_ID = ?";
            
            $stmtCompra = $conn->prepare($sqlCrearCompra);
            $stmtCompra->bind_param("ii", $usuarioID, $usuarioID);
            $stmtCompra->execute();
            $compraID = $conn->insert_id;
            
            // Crear los detalles de la compra
            $sqlDetalles = "INSERT INTO Detalle_Compra (compra_ID, producto_ID, cantidad, precio_unitario) 
                            SELECT ?, c.producto_ID, c.cantidad, p.precio 
                            FROM Carro c 
                            JOIN Producto p ON c.producto_ID = p.producto_ID 
                            WHERE c.usuario_ID = ?";
            
            $stmtDetalles = $conn->prepare($sqlDetalles);
            $stmtDetalles->bind_param("ii", $compraID, $usuarioID);
            $stmtDetalles->execute();
            
            // Actualizar el stock de los productos
            $sqlProductos = "SELECT producto_ID, cantidad FROM Carro WHERE usuario_ID = ?";
            $stmtProductos = $conn->prepare($sqlProductos);
            $stmtProductos->bind_param("i", $usuarioID);
            $stmtProductos->execute();
            $resultProductos = $stmtProductos->get_result();
            
            while ($row = $resultProductos->fetch_assoc()) {
                $this->product->updateStock($row['producto_ID'], $row['cantidad']);
            }
            
            // Limpiar el carrito
            $this->cart->clearCart($correo);
            
            // Confirmar transacción
            $conn->commit();
            
            return ['success' => true];
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            return ['success' => false, 'message' => 'Error al crear la compra: ' . $e->getMessage()];
        }
    }
    
    public function getPurchaseHistory($correo) {
        $conn = $this->db->getConnection();
        
        // Obtener el ID del usuario
        $usuarioID = $this->getUserIdByEmail($correo);
        if (!$usuarioID) {
            return [];
        }
        
        $sql = "SELECT c.compra_ID, c.fecha_compra, c.total
                FROM Compra c
                WHERE c.usuario_ID = ?
                ORDER BY c.fecha_compra DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $purchases = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $purchases[] = $row;
            }
        }
        
        return $purchases;
    }
    
    public function getPurchaseDetails($compraID) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT dc.detalle_compra_ID, p.nombre_producto, dc.cantidad, dc.precio_unitario, 
                (dc.cantidad * dc.precio_unitario) AS subtotal
                FROM Detalle_Compra dc
                JOIN Producto p ON dc.producto_ID = p.producto_ID
                WHERE dc.compra_ID = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $compraID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $details = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $details[] = $row;
            }
        }
        
        return $details;
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