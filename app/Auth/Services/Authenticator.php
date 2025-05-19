<?php
// Modificar el Authenticator.php para mejorar la consistencia de la autenticación

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
        
        // Agregar también las variables de sesión en formato alternativo
        // para mantener compatibilidad con el resto del sistema
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['email'] = $user->getEmail();
        
        return true;
    }
    
    public function logout(): bool
    {
        $this->sessionHandler->destroy();
        return true;
    }
    
    public function isAuthenticated(): bool
    {
        // Verificar tanto con correo como con user_id
        return $this->sessionHandler->has('correo') || 
               $this->sessionHandler->has('usuario_id') ||
               isset($_SESSION['user_id']);
    }
    
    public function getCurrentUserEmail(): ?string
    {
        return $_SESSION['email'] ?? $this->sessionHandler->get('correo');
    }
    
    public function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? $this->sessionHandler->get('usuario_id');
    }
}