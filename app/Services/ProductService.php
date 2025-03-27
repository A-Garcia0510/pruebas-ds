<?php
namespace App\Services;

use App\Interfaces\ProductInterface;
use App\Repositories\ProductRepository;

class ProductService {
    private $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(): array {
        return $this->productRepository->findAll();
    }

    public function getProductsByCategory(string $category): array {
        return $this->productRepository->findByCategory($category);
    }

    public function getAllCategories(): array {
        return $this->productRepository->findAllCategories();
    }

    public function updateProductStock(int $productId, int $quantity): bool {
        return $this->productRepository->updateStock($productId, $quantity);
    }
}
