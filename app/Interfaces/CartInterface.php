<?php
namespace Interfaces;

interface CartInterface {
    public function addProduct(int $userId, int $productId, int $quantity): bool;
    public function removeProduct(int $userId, int $productId): bool;
    public function getCartContents(int $userId): array;
    public function clearCart(int $userId): bool;
    public function calculateTotal(int $userId): float;
}