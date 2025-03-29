<?php
// src/Shop/Exceptions/CheckoutException.php
namespace App\Shop\Exceptions;

/**
 * Excepción lanzada cuando ocurre un error en el proceso de checkout/compra
 */
class CheckoutException extends \Exception
{
    private $cartItems;

    public function __construct(
        string $message = "Error en el proceso de compra", 
        int $code = 500, 
        \Throwable $previous = null,
        array $cartItems = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->cartItems = $cartItems;
    }

    /**
     * Obtiene los items del carrito en el momento del error
     *
     * @return array
     */
    public function getCartItems(): array
    {
        return $this->cartItems;
    }
}