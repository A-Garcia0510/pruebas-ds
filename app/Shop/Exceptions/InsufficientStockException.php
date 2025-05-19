<?php
// src/Shop/Exceptions/InsufficientStockException.php
namespace App\Shop\Exceptions;

/**
 * Excepción lanzada cuando no hay suficiente stock de un producto
 */
class InsufficientStockException extends \Exception
{
    private $productId;
    private $requestedQuantity;
    private $availableQuantity;

    public function __construct(
        string $message = "Stock insuficiente", 
        int $code = 400, 
        \Throwable $previous = null,
        ?int $productId = null,
        ?int $requestedQuantity = null,
        ?int $availableQuantity = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->productId = $productId;
        $this->requestedQuantity = $requestedQuantity;
        $this->availableQuantity = $availableQuantity;
    }

    /**
     * Obtiene el ID del producto con stock insuficiente
     *
     * @return int|null
     */
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    /**
     * Obtiene la cantidad solicitada
     *
     * @return int|null
     */
    public function getRequestedQuantity(): ?int
    {
        return $this->requestedQuantity;
    }

    /**
     * Obtiene la cantidad disponible
     *
     * @return int|null
     */
    public function getAvailableQuantity(): ?int
    {
        return $this->availableQuantity;
    }
}