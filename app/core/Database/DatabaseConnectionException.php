<?php
// app/core/Database/DatabaseConnectionException.php
namespace App\Core\Database;

use Exception;

class DatabaseConnectionException extends Exception
{
    /**
     * Constructor de la excepción de conexión
     * 
     * @param string $message El mensaje de error
     * @param int $code El código de error
     * @param Exception|null $previous La excepción anterior
     */
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct("Error de conexión a la base de datos: " . $message, $code, $previous);
    }
}