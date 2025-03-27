<?php
namespace App\Services;

use App\Repositories\PurchaseRepository;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

class PurchaseService {
    private $purchaseRepository;
    private $cartRepository;
    private $productRepository;

    public function __construct(
        PurchaseRepository $purchaseRepository, 
        CartRepository $cartRepository,
        ProductRepository $productRepository
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    public function processPurchase(string $email): array {
        if ($this->cartRepository->isEmpty($email)) {
            return ['success' => false, 'message' => 'El carrito está vacío.'];
        }

        $cartItems = $this->cartRepository->findByEmail($email);
        
        $purchase = $this->purchaseRepository->create($email, $cartItems);
        
        if ($purchase['success']) {
            // Actualizar stock de productos
            foreach ($cartItems as $item) {
                $this->productRepository->updateStock($item['producto_ID'], $item['cantidad']);
            }
            
            // Limpiar carrito
            $this->cartRepository->clear($email);
        }

        return $purchase;
    }

    public function getPurchaseHistory(string $email): array {
        return $this->purchaseRepository->findByEmail($email);
    }
}
