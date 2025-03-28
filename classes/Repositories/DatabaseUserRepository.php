<?php
namespace Repositories;

use Interfaces\UserRepositoryInterface;
use Entities\User;
use Entities\Email;
use Database;

class DatabaseUserRepository implements UserRepositoryInterface {
    private Database $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    public function findByEmail(Email $email): ?User {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $email->getValue());
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        $userData = $result->fetch_assoc();
        return new User(
            $userData['nombre'], 
            $userData['apellidos'], 
            new Email($userData['correo']), 
            new Password($userData['contraseña'])
        );
    }

    public function save(User $user): void {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO Usuario (nombre, apellidos, correo, contraseña) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssss", 
            $user->getName(), 
            $user->getLastName(), 
            $user->getEmail()->getValue(), 
            $user->getPassword()->getHashedValue()
        );
        $stmt->execute();
    }

    public function emailExists(Email $email): bool {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $email->getValue());
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_array()[0] > 0;
    }
}