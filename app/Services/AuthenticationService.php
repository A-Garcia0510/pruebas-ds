<?php
namespace App\Services;

use App\Interfaces\PasswordHasherInterface;
use App\Repositories\UserRepository;
use App\Security\SessionManager;
use Exceptions\UserException;

class AuthenticationService {
    private UserRepository $userRepository;
    private PasswordHasherInterface $passwordHasher;
    private SessionManager $sessionManager;

    public function __construct(
        UserRepository $userRepository,
        PasswordHasherInterface $passwordHasher,
        SessionManager $sessionManager
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Iniciar sesión de usuario
     * @param string $email Correo electrónico
     * @param string $password Contraseña
     * @return bool Resultado del inicio de sesión
     * @throws UserException Si las credenciales son inválidas
     */
    public function login(string $email, string $password): bool {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$this->passwordHasher->verify($password, $user['password'])) {
            throw UserException::invalidCredentials();
        }

        // Iniciar sesión
        $this->sessionManager->start((int)$user['id']);

        return true;
    }

    /**
     * Registrar nuevo usuario
     * @param array $userData Datos del usuario
     * @return array Resultado del registro
     * @throws UserException Si el registro falla
     */
    public function register(array $userData): array {
        // Verificar si el email ya existe
        $existingUser = $this->userRepository->findByEmail($userData['email']);
        if ($existingUser) {
            throw UserException::emailAlreadyExists($userData['email']);
        }

        // Hashear contraseña
        $userData['password'] = $this->passwordHasher->hash($userData['password']);

        // Intentar crear usuario
        $result = $this->userRepository->create($userData);

        if (!$result) {
            throw UserException::registrationFailed();
        }

        return [
            'success' => true,
            'message' => 'Registro exitoso'
        ];
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void {
        $this->sessionManager->destroy();
    }

    /**
     * Obtener datos de usuario actual
     * @return array|null Datos del usuario
     */
    public function getCurrentUser(): ?array {
        $userId = $this->sessionManager->getUserId();
        
        if (!$userId) {
            return null;
        }

        // Implementar método para obtener usuario por ID en UserRepository
        // return $this->userRepository->findById($userId);
        return null; // Placeholder
    }
}