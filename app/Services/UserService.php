<?php
namespace Services;

require_once "../app/Interfaces/UserInterface";
require_once "../app/Interfaces/UserRepositoryInterface";
require_once "../app/Exceptions/UserException";

class UserService implements UserInterface {
    private $userRepository;
    private $passwordHasher;

    public function __construct(
        UserRepositoryInterface $userRepository, 
        PasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function login(string $email, string $password): bool {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$this->passwordHasher->verify($password, $user['password'])) {
            throw UserException::invalidCredentials();
        }

        // Start session logic
        $_SESSION['user_id'] = $user['id'];
        return true;
    }

    public function register(string $name, string $lastName, string $email, string $password): array {
        if ($this->emailExists($email)) {
            throw UserException::emailAlreadyExists($email);
        }

        $hashedPassword = $this->passwordHasher->hash($password);

        $userData = [
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $hashedPassword
        ];

        $result = $this->userRepository->create($userData);

        if (!$result) {
            throw UserException::registrationFailed();
        }

        return [
            'success' => true, 
            'message' => 'Registration successful'
        ];
    }

    // ... other methods
}