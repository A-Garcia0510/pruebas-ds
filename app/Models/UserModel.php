<?php
namespace App\Models;

class User {
    private int $id;
    private string $name;
    private string $lastName;
    private string $email;
    private string $password;

    public function __construct(
        string $name, 
        string $lastName, 
        string $email, 
        string $password
    ) {
        $this->name = $name;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $this->hashPassword($password);
    }

    private function hashPassword(string $password): string {
        // Implementar hash seguro
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $plainPassword): bool {
        return password_verify($plainPassword, $this->password);
    }

    // Getters y setters con validaciones
    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getFullName(): string {
        return "{$this->name} {$this->lastName}";
    }
}