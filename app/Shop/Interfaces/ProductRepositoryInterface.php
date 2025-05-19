<?php
// src/Shop/Interfaces/ProductRepositoryInterface.php
namespace App\Shop\Interfaces;

use App\Shop\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Encuentra un producto por su ID
     * 
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product;
    
    /**
     * Obtiene todos los productos
     * 
     * @return array
     */
    public function findAll(): array;
    
    /**
     * Encuentra productos por categoría
     * 
     * @param string $category
     * @return array
     */
    public function findByCategory(string $category): array;
    
    /**
     * Obtiene todas las categorías disponibles
     * 
     * @return array
     */
    public function getAllCategories(): array;
    
    /**
     * Actualiza la cantidad de un producto
     * 
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateStock(int $id, int $quantity): bool;
    
    /**
     * Guarda un producto
     * 
     * @param Product $product
     * @return bool
     */
    public function save(Product $product): bool;
}