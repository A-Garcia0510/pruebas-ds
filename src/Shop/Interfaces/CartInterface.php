<?php
// src/Shop/Interfaces/CartInterface.php
namespace App\Shop\Interfaces;

use App\Shop\Models\CartItem;

interface CartInterface
{
    /**
     * Agrega un producto al carrito
     * 
     * @param string $userId
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function addItem(string $userId, int $productId, int $quantity): bool;
    
    /**
     * Elimina un producto del carrito
     * 
     * @param string $userId
     * @param int $productId
     * @return bool
     */
    public function removeItem(string $userId, int $productId): bool;
    
    /**
     * Obtiene los items del carrito de un usuario
     * 
     * @param string $userId
     * @return array
     */
    public function getItems(string $userId): array;
    
    /**
     * Limpia el carrito de un usuario
     * 
     * @param string $userId
     * @return bool
     */
    public function clear(string $userId): bool;
    
    /**
     * Obtiene el total del carrito
     * 
     * @param string $userId
     * @return float
     */
    public function getTotal(string $userId): float;
}