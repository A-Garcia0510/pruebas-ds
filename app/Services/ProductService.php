<?php
namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use App\Exceptions\ProductException;

class ProductService {
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository) {
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

    public function getProductById(int $productId): array {
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw ProductException::productNotFound($productId);
        }

        return $product;
    }

    public function updateProductStock(int $productId, int $quantity): bool {
        $product = $this->getProductById($productId);
        
        if ($quantity > $product['cantidad']) {
            throw ProductException::insufficientStock(
                $productId, 
                $quantity, 
                $product['cantidad']
            );
        }

        return $this->productRepository->updateStock($productId, $quantity);
    }
}