<?php
interface UserRepositoryInterface {
    public function findByEmail(string $email): ?array;
    public function create(array $userData): bool;
    public function existsByEmail(string $email): bool;
}

class UserRepository implements UserRepositoryInterface {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function create(array $userData): bool {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO Usuario (nombre, apellidos, correo, contraseña) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $userData['nombre'], $userData['apellidos'], $userData['correo'], $userData['contraseña']);
        
        return $stmt->execute();
    }

    public function existsByEmail(string $email): bool {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
}