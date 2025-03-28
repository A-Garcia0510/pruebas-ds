<?php
namespace Services;

use Interfaces\UserValidationInterface;
use Exceptions\ValidationException;

class UserValidationService implements UserValidationInterface {
    public function validateEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("El formato del correo electrónico no es válido.");
        }
    }

    public function validatePassword(string $password): void {
        if (strlen($password) < 8) {
            throw new ValidationException("La contraseña debe tener al menos 8 caracteres.");
        }
    }
}