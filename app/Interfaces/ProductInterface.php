<?php
namespace Interfaces;

interface ProductInterface {
    public function getAllProducts(): array;
    public function getProductsByCategory(string $categoria): array;
    public function getAllCategories(): array;
    public function updateStock(int $productoID, int $cantidad): bool;
    public function getProductById(int $productoID): ?array;
}