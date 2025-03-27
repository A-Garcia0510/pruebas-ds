<?php
namespace Services;

use app\Interfaces\CartInterface;
use app\Repositories\CartRepository;
use app\Exceptions\CartException;

class CartService implements CartInterface {
    private CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepository) {
        $this->cartRepository = $cartRepository;
    }

    public function addProduct(int $userId, int $productId, int $quantity): bool {
        try {
            return $this->cartRepository->add($userId, $productId, $quantity);
        } catch (\Exception $e) {
            throw new CartException("Error al agregar producto: " . $e->getMessage());
        }
    }

    public function removeProduct(int $userId, int $productId): bool {
        try {
            return $this->cartRepository->remove($userId, $productId);
        } catch (\Exception $e) {
            throw new CartException("Error al eliminar producto: " . $e->getMessage());
        }
    }

    public function getCartContents(int $userId): array {
        try {
            return $this->cartRepository->findByUser($userId);
        } catch (\Exception $e) {
            throw new CartException("Error al obtener contenido del carrito: " . $e->getMessage());
        }
    }

    public function clearCart(int $userId): bool {
        try {
            return $this->cartRepository->clear($userId);
        } catch (\Exception $e) {
            throw new CartException("Error al limpiar el carrito: " . $e->getMessage());
        }
    }

    public function calculateTotal(int $userId): float {
        try {
            $cartItems = $this->getCartContents($userId);
            $total = 0.0;
            
            foreach ($cartItems as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            
            return $total;
        } catch (\Exception $e) {
            throw new CartException("Error al calcular total: " . $e->getMessage());
        }
    }
}