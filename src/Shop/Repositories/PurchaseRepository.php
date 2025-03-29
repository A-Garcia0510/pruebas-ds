<?php
// src/Shop/Repositories/PurchaseRepository.php
namespace App\Shop\Repositories;

use App\Core\Database\DatabaseInterface;
use App\Shop\Models\Purchase;
use App\Shop\Models\PurchaseDetail;
use DateTime;

class PurchaseRepository
{
    private $db;
    
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }
    
    /**
     * Guarda una compra y sus detalles
     * 
     * @param Purchase $purchase
     * @return bool
     */
    public function save(Purchase $purchase): bool
    {
        $conn = $this->db->getConnection();
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar la compra
            $stmt = $conn->prepare("INSERT INTO Pedido (usuario_email, total, fecha, estado) VALUES (?, ?, ?, ?)");
            $userId = $purchase->getUserId();
            $total = $purchase->getTotal();
            $date = $purchase->getDate()->format('Y-m-d H:i:s');
            $status = $purchase->getStatus();
            
            $stmt->bind_param("sdss", $userId, $total, $date, $status);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error al crear la compra");
            }
            
            $purchaseId = $conn->insert_id;
            $purchase->setId($purchaseId);
            
            // Insertar los detalles
            foreach ($purchase->getDetails() as $detail) {
                $detail->setPurchaseId($purchaseId);
                
                $stmt = $conn->prepare("INSERT INTO DetallePedido (pedido_ID, producto_ID, nombre_producto, cantidad, precio) VALUES (?, ?, ?, ?, ?)");
                $productId = $detail->getProductId();
                $productName = $detail->getProductName();
                $quantity = $detail->getQuantity();
                $price = $detail->getPrice();
                
                $stmt->bind_param("iisid", $purchaseId, $productId, $productName, $quantity, $price);
                
                if (!$stmt->execute()) {
                    throw new \Exception("Error al crear el detalle de la compra");
                }
            }
            
            // Confirmar transacción
            $conn->commit();
            return true;
            
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            return false;
        }
    }
    
    /**
     * Obtiene las compras de un usuario
     * 
     * @param string $userId
     * @return array
     */
    public function findByUserId(string $userId): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Pedido WHERE usuario_email = ? ORDER BY fecha DESC");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $purchases = [];
        while ($data = $result->fetch_assoc()) {
            $date = new DateTime($data['fecha']);
            $purchase = new Purchase(
                $data['pedido_ID'],
                $data['usuario_email'],
                $data['total'],
                $date,
                $data['estado']
            );
            
            // Obtener los detalles de la compra
            $details = $this->findDetailsByPurchaseId($data['pedido_ID']);
            $purchase->setDetails($details);
            
            $purchases[] = $purchase;
        }
        
        return $purchases;
    }
    
    /**
     * Obtiene una compra por su ID
     * 
     * @param int $id
     * @return Purchase|null
     */
    public function findById(int $id): ?Purchase
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Pedido WHERE pedido_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $data = $result->fetch_assoc();
        $date = new DateTime($data['fecha']);
        $purchase = new Purchase(
            $data['pedido_ID'],
            $data['usuario_email'],
            $data['total'],
            $date,
            $data['estado']
        );
        
        // Obtener los detalles de la compra
        $details = $this->findDetailsByPurchaseId($id);
        $purchase->setDetails($details);
        
        return $purchase;
    }
    
    /**
     * Obtiene los detalles de una compra
     * 
     * @param int $purchaseId
     * @return array
     */
    private function findDetailsByPurchaseId(int $purchaseId): array
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM DetallePedido WHERE pedido_ID = ?");
        $stmt->bind_param("i", $purchaseId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $details = [];
        while ($data = $result->fetch_assoc()) {
            $details[] = new PurchaseDetail(
                $data['detalle_ID'],
                $data['pedido_ID'],
                $data['producto_ID'],
                $data['nombre_producto'],
                $data['cantidad'],
                $data['precio']
            );
        }
        
        return $details;
    }
}