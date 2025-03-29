<?php
// src/Auth/Services/Authenticator.php
namespace App\Auth\Services;

use App\Auth\Interfaces\AuthenticatorInterface;
use App\Auth\Interfaces\SessionHandlerInterface;
use App\Auth\Interfaces\UserRepositoryInterface;
use App\Auth\Exceptions\AuthenticationException;

class Authenticator implements AuthenticatorInterface
{
    private $userRepository;
    private $sessionHandler;
    
    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionHandlerInterface $sessionHandler
    ) {
        $this->userRepository = $userRepository;
        $this->sessionHandler = $sessionHandler;
        $this->sessionHandler->startSession();
    }
    
    public function authenticate(string $email, string $password): bool
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!$user->validatePassword($password)) {
            return false;
        }
        
        // Almacenar información del usuario en la sesión
        $this->sessionHandler->set('correo', $user->getEmail());
        $this->sessionHandler->set('usuario_id', $user->getId());
        $this->sessionHandler->set('mensaje', 'Inicio de sesión exitoso.');
        
        return true;
    }
    
    public function logout(): bool
    {
        $this->sessionHandler->destroy();
        return true;
    }
    
    public function isAuthenticated(): bool
    {
        return $this->sessionHandler->has('correo');
    }
    
    public function getCurrentUserEmail(): ?string
    {
        return $this->sessionHandler->get('correo');
    }
}