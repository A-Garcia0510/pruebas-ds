<?php
namespace Exceptions;

class UserException extends \Exception {
    public static function invalidCredentials(): self {
        return new self("Invalid login credentials");
    }

    public static function emailAlreadyExists(string $email): self {
        return new self("Email {$email} is already registered");
    }

    public static function registrationFailed(): self {
        return new self("User registration failed");
    }
}