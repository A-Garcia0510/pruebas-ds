<?php
namespace App\Repositories;

use App\Models\Purchase;
use App\Models\User;
use App\Config\Database;

class PurchaseRepository {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function save(Purchase $purchase): bool {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();

            // Guardar compra
            $stmt = $conn->prepare(
                "INSERT INTO Compra (usuario_ID, fecha_compra, total, estado) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $purchase->getUser()->getId(),
                $purchase->getPurchaseDate()->format('Y-m-d H:i:s'),
                $purchase->getTotal(),
                $purchase->getStatus()
            ]);

            $purchaseId = $conn->lastInsertId();
            $purchase->setId((int)$purchaseId);

            // Guardar detalles de compra
            $detailStmt = $conn->prepare(
                "INSERT INTO Detalle_Compra (compra_ID, producto_ID, cantidad, precio_unitario) 
                 VALUES (?, ?, ?, ?)"
            );

            foreach ($purchase->getCart()->getItems() as $item) {
                $detailStmt->execute([
                    $purchaseId,
                    $item['product']->getId(),
                    $item['quantity'],
                    $item['product']->getPrice()
                ]);
            }

            $conn->commit();
            return true;
        } catch (\Exception $e) {
            $conn->rollBack();
            // Log error
            return false;
        }
    }

    public function findByUser(User $user): array {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare(
            "SELECT * FROM Compra WHERE usuario_ID = ? ORDER BY fecha_compra DESC"
        );
        $stmt->execute([$user->getId()]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById(int $purchaseId): ?Purchase {
        // Implementación para encontrar compra por ID
        // Requerirá mapeo de datos a objetos Purchase
        return null;
    }
}
