<?php

namespace Interfaces;

interface UserValidationInterface {
    public function validateEmail(string $email): bool;
    public function validatePassword(string $password): bool;
    public function validateUsername(string $username): bool;
}
