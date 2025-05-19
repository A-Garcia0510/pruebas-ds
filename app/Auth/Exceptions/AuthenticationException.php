<?php
// src/Auth/Exceptions/AuthenticationException.php
namespace App\Auth\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    /**
     * Constructor para la excepción de autenticación
     * 
     * @param string $message El mensaje de error
     * @param int $code El código de error
     * @param Exception|null $previous La excepción anterior
     */
    public function __construct(string $message = "Error de autenticación", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}