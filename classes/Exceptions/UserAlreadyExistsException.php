<?php
namespace Exceptions;

use Exception;

class UserAlreadyExistsException extends Exception {
    protected $message = "El usuario ya existe.";
}