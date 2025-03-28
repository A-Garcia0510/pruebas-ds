<?php
namespace App\Interfaces;

interface UserRepositoryInterface {
    /**
     * Find a user by email
     * 
     * @param string $email User's email
     * @return array|null User data or null if not found
     */
    public function findByEmail(string $email): ?array;

    /**
     * Create a new user
     * 
     * @param array $userData User registration data
     * @return bool Whether user creation was successful
     */
    public function create(array $userData): bool;

    /**
     * Update user information
     * 
     * @param string $email User's email
     * @param array $updateData Data to update
     * @return bool Whether update was successful
     */
    public function update(string $email, array $updateData): bool;

    /**
     * Check if a user with the given email exists
     * 
     * @param string $email Email to check
     * @return bool Whether the email exists
     */
    public function emailExists(string $email): bool;
}