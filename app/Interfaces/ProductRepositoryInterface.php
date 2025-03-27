<?php
namespace App\Interfaces;

interface ProductRepositoryInterface {
    public function findAll(): array;
    public function findByCategory(string $category): array;
    public function findAllCategories(): array;
    public function updateStock(int $productId, int $quantity): bool;
    public function findById(int $productId): ?array;
}