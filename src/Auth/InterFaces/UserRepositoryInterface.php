<?php
// src/Auth/Interfaces/UserRepositoryInterface.php
namespace App\Auth\Interfaces;

use App\Auth\Models\User;

interface UserRepositoryInterface
{
    /**
     * Busca un usuario por su email
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;
    
    /**
     * Busca un usuario por su ID
     * 
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;
    
    /**
     * Guarda un usuario
     * 
     * @param User $user
     * @return bool
     */
    public function save(User $user): bool;
    
    /**
     * Verifica si el email ya está registrado
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool;
}