<?php
// src/Shop/Exceptions/ProductNotFoundException.php
namespace App\Shop\Exceptions;

/**
 * Excepción lanzada cuando un producto no se encuentra en el repositorio
 */
class ProductNotFoundException extends \Exception
{
    public function __construct(string $message = "Producto no encontrado", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}