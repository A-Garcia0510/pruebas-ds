<?php
namespace Interfaces;

interface UserRepositoryInterface {
    public function findByEmail(string $email): ?array;
    public function create(array $userData): bool;
    public function update(string $email, array $updateData): bool;
}