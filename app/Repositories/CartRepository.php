<?php
namespace Repositories;

use app\Config\Database;
use PDO;

class CartRepository {
    private PDO $connection;

    public function __construct(Database $database) {
        $this->connection = $database->getConnection();
    }

    public function add(int $userId, int $productId, int $quantity): bool {
        $stmt = $this->connection->prepare(
            "INSERT INTO cart (user_id, product_id, quantity) 
             VALUES (:user_id, :product_id, :quantity) 
             ON DUPLICATE KEY UPDATE quantity = quantity + :quantity"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId,
            ':quantity' => $quantity
        ]);
    }

    public function remove(int $userId, int $productId): bool {
        $stmt = $this->connection->prepare(
            "DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':product_id' => $productId
        ]);
    }

    public function findByUser(int $userId): array {
        $stmt = $this->connection->prepare(
            "SELECT p.id, p.name, p.price, c.quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = :user_id"
        );

        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clear(int $userId): bool {
        $stmt = $this->connection->prepare(
            "DELETE FROM cart WHERE user_id = :user_id"
        );

        return $stmt->execute([':user_id' => $userId]);
    }
}