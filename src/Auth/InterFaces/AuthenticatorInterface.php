<?php
// src/Auth/Interfaces/AuthenticatorInterface.php
namespace App\Auth\Interfaces;

interface AuthenticatorInterface
{
    /**
     * Autentica un usuario con sus credenciales
     * 
     * @param string $email
     * @param string $password
     * @return bool Si la autenticación fue exitosa
     */
    public function authenticate(string $email, string $password): bool;
    
    /**
     * Cierra la sesión del usuario actual
     * 
     * @return bool Si el cierre de sesión fue exitoso
     */
    public function logout(): bool;
    
    /**
     * Verifica si el usuario está autenticado
     * 
     * @return bool
     */
    public function isAuthenticated(): bool;
}