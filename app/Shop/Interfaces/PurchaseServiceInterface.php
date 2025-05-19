<?php
// src/Shop/Interfaces/PurchaseServiceInterface.php
namespace App\Shop\Interfaces;

use App\Shop\Models\Purchase;

interface PurchaseServiceInterface
{
    /**
     * Crea una nueva compra a partir del carrito del usuario
     * 
     * @param string $userId
     * @return bool
     */
    public function createPurchase(string $userId): bool;
    
    /**
     * Obtiene las compras de un usuario
     * 
     * @param string $userId
     * @return array
     */
    public function getUserPurchases(string $userId): array;
    
    /**
     * Obtiene los detalles de una compra
     * 
     * @param int $purchaseId
     * @return array
     */
    public function getPurchaseDetails(int $purchaseId): array;
}