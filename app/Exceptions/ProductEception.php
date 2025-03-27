<?php
namespace Exceptions;

class ProductException extends \Exception {
    public static function productNotFound(int $productId) {
        return new self("Producto con ID {$productId} no encontrado.");
    }

    public static function insufficientStock(int $productId, int $requestedQuantity, int $availableStock) {
        return new self("Stock insuficiente para el producto {$productId}. Solicitado: {$requestedQuantity}, Disponible: {$availableStock}");
    }
}