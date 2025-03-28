<?php
namespace Entities;

class User {
    private string $id;
    private string $name;
    private string $lastName;
    private Email $email;
    private Password $password;

    public function __construct(
        string $name, 
        string $lastName, 
        Email $email, 
        Password $password
    ) {
        $this->name = $name;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
    }

    // Getters and immutable setters
    public function getName(): string { return $this->name; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): Email { return $this->email; }
}

// Value Objects
class Email {
    private string $value;

    public function __construct(string $email) {
        $this->validate($email);
        $this->value = $email;
    }

    private function validate(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format");
        }
    }

    public function getValue(): string { return $this->value; }
}

class Password {
    private string $hashedValue;

    public function __construct(string $plainPassword) {
        $this->hashedValue = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function verify(string $plainPassword): bool {
        return password_verify($plainPassword, $this->hashedValue);
    }
}