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
            $stmt = $conn->prepare("INSERT INTO Compra (usuario_ID, fecha_compra, total) VALUES (?, ?, ?)");
            $userId = $purchase->getUserId();
            $total = $purchase->getTotal();
            $date = $purchase->getDate()->format('Y-m-d H:i:s');
            
            $stmt->bind_param("isd", $userId, $date, $total);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error al crear la compra");
            }
            
            $purchaseId = $conn->insert_id;
            $purchase->setId($purchaseId);
            
            // Insertar los detalles
            foreach ($purchase->getDetails() as $detail) {
                $detail->setPurchaseId($purchaseId);
                
                $stmt = $conn->prepare("INSERT INTO Detalle_Compra (compra_ID, producto_ID, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                $productId = $detail->getProductId();
                $quantity = $detail->getQuantity();
                $price = $detail->getPrice();
                
                $stmt->bind_param("iiid", $purchaseId, $productId, $quantity, $price);
                
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
        $stmt = $conn->prepare("SELECT * FROM Compra WHERE usuario_ID = ? ORDER BY fecha_compra DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $purchases = [];
        while ($data = $result->fetch_assoc()) {
            $date = new DateTime($data['fecha_compra']);
            $purchase = new Purchase(
                $data['compra_ID'],
                $data['usuario_ID'],
                $data['total'],
                $date,
                $data['compra_ID'] ? Purchase::STATUS_COMPLETED : Purchase::STATUS_PENDING
            );
            
            // Obtener los detalles de la compra
            $details = $this->findDetailsByPurchaseId($data['compra_ID']);
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
        $stmt = $conn->prepare("SELECT * FROM Compra WHERE compra_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $data = $result->fetch_assoc();
        $date = new DateTime($data['fecha_compra']);
        $purchase = new Purchase(
            $data['compra_ID'],
            $data['usuario_ID'],
            $data['total'],
            $date,
            $data['compra_ID'] ? Purchase::STATUS_COMPLETED : Purchase::STATUS_PENDING
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
        $stmt = $conn->prepare("
            SELECT dc.*, p.nombre_producto 
            FROM Detalle_Compra dc
            JOIN Producto p ON dc.producto_ID = p.producto_ID
            WHERE dc.compra_ID = ?
        ");
        $stmt->bind_param("i", $purchaseId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $details = [];
        while ($data = $result->fetch_assoc()) {
            $details[] = new PurchaseDetail(
                $data['detalle_compra_ID'],
                $data['compra_ID'],
                $data['producto_ID'],
                $data['nombre_producto'], // Obtenido del JOIN con la tabla Producto
                $data['cantidad'],
                $data['precio_unitario']
            );
        }
        
        return $details;
    }
}