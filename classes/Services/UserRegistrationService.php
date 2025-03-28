<?php
namespace Services;

use Interfaces\UserRepositoryInterface;
use Interfaces\UserValidationInterface;
use Entities\User;
use Exceptions\UserAlreadyExistsException;


class UserRegistrationService {
    private UserRepositoryInterface $userRepository;
    private UserValidationInterface $userValidation;

    public function __construct(
        UserRepositoryInterface $userRepository, 
        UserValidationInterface $userValidation
    ) {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
    }

    public function register(User $user): void {
        // Validate user data
        $this->userValidation->validateRegistration($user);

        // Check if email already exists
        if ($this->userRepository->emailExists($user->getEmail())) {
            throw new UserAlreadyExistsException(
                "A user with this email already exists."
            );
        }

        // Save user
        $this->userRepository->save($user);
    }
}