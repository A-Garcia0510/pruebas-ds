<?php
// src/Auth/AuthFactory.php
namespace App\Auth;

use App\Auth\Interfaces\AuthenticatorInterface;
use App\Auth\Interfaces\SessionHandlerInterface;
use App\Auth\Interfaces\UserRepositoryInterface;
use App\Auth\Services\Authenticator;
use App\Auth\Services\SessionHandler;
use App\Auth\Repositories\UserRepository;
use App\Core\Database\DatabaseInterface;

class AuthFactory
{
    /**
     * Crea y retorna un manejador de sesiones
     * 
     * @return SessionHandlerInterface
     */
    public static function createSessionHandler(): SessionHandlerInterface
    {
        return new SessionHandler();
    }
    
    /**
     * Crea y retorna un repositorio de usuarios
     * 
     * @param DatabaseInterface $database
     * @return UserRepositoryInterface
     */
    public static function createUserRepository(DatabaseInterface $database): UserRepositoryInterface
    {
        return new UserRepository($database);
    }
    
    /**
     * Crea y retorna un autenticador
     * 
     * @param DatabaseInterface $database
     * @return AuthenticatorInterface
     */
    public static function createAuthenticator(DatabaseInterface $database): AuthenticatorInterface
    {
        $userRepository = self::createUserRepository($database);
        $sessionHandler = self::createSessionHandler();
        
        return new Authenticator($userRepository, $sessionHandler);
    }
}