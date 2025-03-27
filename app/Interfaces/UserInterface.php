<?php
namespace Interfaces;

interface UserInterface {
    public function login(string $email, string $password): bool;
    public function register(string $name, string $lastName, string $email, string $password): array;
    public function getUserData(string $email): ?array;
    public function emailExists(string $email): bool;
    public function logout(): void;
}