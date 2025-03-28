<?php
namespace Exceptions;

use Exception;

class InvalidCredentialsException extends Exception {
    protected $message = "Credenciales inválidas.";
}