<?php
interface UserValidatorInterface {
    public function validatePassword(string $password): bool;
    public function validateEmail(string $email): bool;
}

class UserValidator implements UserValidatorInterface {
    public function validatePassword(string $password): bool {
        return strlen($password) >= 8;
    }

    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}