<?php
namespace App\Shop\Services;

use App\Shop\Repositories\ProductRepository;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Shop\Models\Product;

class ProductService {
    private $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    /**
     * Obtiene un producto por su ID
     * 
     * @param int $productId
     * @return Product
     * @throws ProductNotFoundException
     */
    public function getProduct($productId) {
        $product = $this->productRepository->findById($productId);
        
        if (!$product) {
            throw new ProductNotFoundException("Producto con ID {$productId} no encontrado");
        }
        
        return $product;
    }

    /**
     * Obtiene todos los productos
     * 
     * @return array
     */
    public function getAllProducts() {
        return $this->productRepository->findAll();
    }

    /**
     * Verifica si hay suficiente stock de un producto
     * 
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws ProductNotFoundException
     */
    public function checkStock($productId, $quantity) {
        $product = $this->getProduct($productId);
        return $product->getStock() >= $quantity;
    }

    /**
     * Actualiza el stock de un producto
     * 
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws ProductNotFoundException
     */
    public function updateStock($productId, $quantity) {
        $product = $this->getProduct($productId);
        $newStock = $product->getStock() - $quantity;
        
        if ($newStock < 0) {
            return false;
        }
        
        return $this->productRepository->updateStock($productId, $quantity);
    }
} 