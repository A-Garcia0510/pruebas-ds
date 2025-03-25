<?php
class UserService implements UserInterface {
    private $userRepository;
    private $userValidator;
    private $sessionManager;

    public function __construct(
        UserRepositoryInterface $userRepository, 
        UserValidatorInterface $userValidator,
        SessionManagerInterface $sessionManager
    ) {
        $this->userRepository = $userRepository;
        $this->userValidator = $userValidator;
        $this->sessionManager = $sessionManager;
    }

    public function login(string $email, string $password): bool {
        $user = $this->userRepository->findByEmail($email);
        
        if ($user && $password === $user['contraseña']) {
            $this->sessionManager->set('correo', $email);
            return true;
        }
        
        return false;
    }

    public function register(array $userData): array {
        if (!$this->userValidator->validatePassword($userData['contraseña'])) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }
        
        if ($this->userRepository->existsByEmail($userData['correo'])) {
            return ['success' => false, 'message' => 'El correo ya está registrado.'];
        }
        
        $success = $this->userRepository->create($userData);
        
        return $success 
            ? ['success' => true, 'message' => 'Registro exitoso. Puedes iniciar sesión ahora.']
            : ['success' => false, 'message' => 'Error al registrar.'];
    }

    public function emailExists(string $email): bool {
        return $this->userRepository->existsByEmail($email);
    }

    public function getUserData(string $email): ?array {
        $user = $this->userRepository->findByEmail($email);
        return $user ? ['nombre' => $user['nombre'], 'apellidos' => $user['apellidos']] : null;
    }

    public function logout(): void {
        $this->sessionManager->destroy();
    }
}