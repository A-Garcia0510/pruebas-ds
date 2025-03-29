<?php
// src/Shop/Services/PurchaseService.php
namespace App\Shop\Services;

use App\Core\Database\DatabaseInterface;
use App\Shop\Interfaces\PurchaseServiceInterface;
use App\Shop\Interfaces\CartInterface;
use App\Shop\Interfaces\ProductRepositoryInterface;
use App\Shop\Models\Purchase;
use App\Shop\Models\PurchaseDetail;
use App\Shop\Repositories\PurchaseRepository;
use App\Shop\Exceptions\CheckoutException;
use DateTime;

class PurchaseService implements PurchaseServiceInterface
{
    private $db;
    private $cartService;
    private $productRepository;
    private $purchaseRepository;
    
    public function __construct(
        DatabaseInterface $db,
        CartInterface $cartService,
        ProductRepositoryInterface $productRepository,
        PurchaseRepository $purchaseRepository
    ) {
        $this->db = $db;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->purchaseRepository = $purchaseRepository;
    }
    
    /**
     * Crea una nueva compra a partir del carrito del usuario
     * 
     * @param string $userId
     * @return bool
     * @throws CheckoutException
     */
    public function createPurchase(string $userId): bool
    {
        $conn = $this->db->getConnection();
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Obtener los items del carrito
            $cartItems = $this->cartService->getItems($userId);
            
            if (empty($cartItems)) {
                throw new CheckoutException("El carrito está vacío");
            }
            
            // Crear la compra
            $purchase = new Purchase(
                null,
                $userId,
                0,
                new DateTime(),
                Purchase::STATUS_PENDING
            );
            
            // Añadir los detalles de la compra
            foreach ($cartItems as $item) {
                $product = $this->productRepository->findById($item->getProductId());
                
                if (!$product) {
                    throw new CheckoutException("El producto {$item->getProductId()} no existe");
                }
                
                if (!$product->hasStock($item->getQuantity())) {
                    throw new CheckoutException("Stock insuficiente para el producto {$product->getName()}");
                }
                
                // Añadir detalle a la compra
                $detail = new PurchaseDetail(
                    null,
                    null,
                    $item->getProductId(),
                    $item->getProductName(),
                    $item->getQuantity(),
                    $item->getProductPrice()
                );
                
                $purchase->addDetail($detail);
                
                // Actualizar el stock del producto
                if (!$this->productRepository->updateStock($item->getProductId(), $item->getQuantity())) {
                    throw new CheckoutException("Error al actualizar el stock del producto {$product->getName()}");
                }
            }
            
            // Calcular el total de la compra
            $purchase->calculateTotal();
            
            // Guardar la compra
            if (!$this->purchaseRepository->save($purchase)) {
                throw new CheckoutException("Error al guardar la compra");
            }
            
            // Limpiar el carrito
            if (!$this->cartService->clear($userId)) {
                throw new CheckoutException("Error al limpiar el carrito");
            }
            
            // Confirmar la transacción
            $conn->commit();
            return true;
            
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();
            
            // Relanzar la excepción como una CheckoutException
            throw new CheckoutException($e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Obtiene las compras de un usuario
     * 
     * @param string $userId
     * @return array
     */
    public function getUserPurchases(string $userId): array
    {
        return $this->purchaseRepository->findByUserId($userId);
    }
    
    /**
     * Obtiene los detalles de una compra
     * 
     * @param int $purchaseId
     * @return array
     * @throws CheckoutException
     */
    public function getPurchaseDetails(int $purchaseId): array
    {
        $purchase = $this->purchaseRepository->findById($purchaseId);
        
        if (!$purchase) {
            throw new CheckoutException("La compra no existe");
        }
        
        return $purchase->getDetails();
    }
}