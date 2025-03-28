<?php
namespace Services;

use Interfaces\UserRepositoryInterface;
use Interfaces\UserValidationInterface;
use Interfaces\AuthenticationServiceInterface;
use Entities\User;
use Entities\Email;
use Exceptions\UserAlreadyExistsException;
use Exceptions\InvalidCredentialsException;

class AuthenticationService implements AuthenticationServiceInterface {
    private UserRepositoryInterface $userRepository;
    private UserValidationInterface $userValidation;

    public function __construct(
        UserRepositoryInterface $userRepository, 
        UserValidationInterface $userValidation
    ) {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
    }

    public function login(Email $email, string $password): User {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new InvalidCredentialsException(
                "No user found with this email."
            );
        }

        $this->userValidation->validateLogin($user, $password);

        // Start session or JWT token generation
        $this->startSession($user);

        return $user;
    }

    public function logout(): void {
        // Implement logout logic
        session_destroy();
    }

    private function startSession(User $user): void {
        session_start();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['email'] = $user->getEmail()->getValue();
    }
}