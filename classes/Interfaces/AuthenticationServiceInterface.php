<?php
namespace Interfaces;

use Entities\User;
use Entities\Email;

interface AuthenticationServiceInterface {
    public function register(User $user): void;
    public function login(Email $email, string $password): User;
    public function logout(): void;
}