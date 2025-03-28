<?php
namespace App\Repositories;

use App\Config\Database;
use App\Interfaces\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface {
    private PDO $connection;

    public function __construct(Database $database) {
        $this->connection = $database->getConnection();
    }

    /**
     * Buscar usuario por email
     * @param string $email Correo electrónico del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->connection->prepare(
            "SELECT * FROM Usuario WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Crear nuevo usuario
     * @param array $userData Datos del usuario
     * @return bool Resultado de la creación
     */
    public function create(array $userData): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO Usuario (nombre, apellido, email, password) 
             VALUES (:name, :last_name, :email, :password)"
        );

        return $stmt->execute([
            ':name' => $userData['name'],
            ':last_name' => $userData['last_name'],
            ':email' => $userData['email'],
            ':password' => $userData['password']
        ]);
    }

    /**
     * Actualizar datos de usuario
     * @param string $email Email del usuario
     * @param array $updateData Datos a actualizar
     * @return bool Resultado de la actualización
     */
    public function update(string $email, array $updateData): bool {
        $fields = [];
        $params = [':email' => $email];

        foreach ($updateData as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql = "UPDATE Usuario SET " . implode(', ', $fields) . " WHERE email = :email";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute($params);
    }
}