<?php
interface UserInterface {
    public function login(string $email, string $password): bool;
    public function register(array $userData): array;
    public function emailExists(string $email): bool;
    public function getUserData(string $email): ?array;
    public function logout(): void;
}