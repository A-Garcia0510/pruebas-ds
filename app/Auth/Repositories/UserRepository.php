<?php
// src/Auth/Repositories/UserRepository.php
namespace App\Auth\Repositories;

use App\Auth\Interfaces\UserRepositoryInterface;
use App\Auth\Models\User;
use App\Core\Database\DatabaseInterface;

class UserRepository implements UserRepositoryInterface
{
    private $db;
    
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }
    
    public function findByEmail(string $email): ?User
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $userData = $result->fetch_assoc();
        
        return new User(
            $userData['usuario_ID'],
            $userData['nombre'],
            $userData['apellidos'],
            $userData['correo'],
            $userData['contraseña']
        );
    }
    
    public function findById(int $id): ?User
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE usuario_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $userData = $result->fetch_assoc();
        
        return new User(
            $userData['usuario_ID'],
            $userData['nombre'],
            $userData['apellidos'],
            $userData['correo'],
            $userData['contraseña']
        );
    }
    
    public function save(User $user): bool
    {
        $conn = $this->db->getConnection();
        
        // Si el usuario ya tiene ID, actualizamos
        if ($user->getId()) {
            $stmt = $conn->prepare("UPDATE usuario SET nombre = ?, apellidos = ?, correo = ?, contraseña = ? WHERE usuario_ID = ?");
            $nombre = $user->getNombre();
            $apellidos = $user->getApellidos();
            $email = $user->getEmail();
            $password = $user->getPassword();
            $id = $user->getId();
            $stmt->bind_param("ssssi", $nombre, $apellidos, $email, $password, $id);
            return $stmt->execute();
        }
        
        // Si no tiene ID, creamos un nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuario (nombre, apellidos, correo, contraseña) VALUES (?, ?, ?, ?)");
        $nombre = $user->getNombre();
        $apellidos = $user->getApellidos();
        $email = $user->getEmail();
        $password = $user->getPassword();
        $stmt->bind_param("ssss", $nombre, $apellidos, $email, $password);
        
        if ($stmt->execute()) {
            $user->setId($conn->insert_id);
            return true;
        }
        
        return false;
    }
    
    public function emailExists(string $email): bool
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
}