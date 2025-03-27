<?php
namespace App\Config;

use App\Interfaces\DatabaseInterface;
use mysqli;

class Database implements DatabaseInterface {
    private static ?self $instance = null;
    private mysqli $connection;
    
    private string $host = "mysql.inf.uct.cl";
    private string $user = "agarcia";
    private string $password = "chuMKZ3EdhJvje706";
    private string $database = "A2024_agarcia";
    
    private function __construct() {
        $this->connection = new mysqli(
            $this->host, 
            $this->user, 
            $this->password, 
            $this->database
        );
        
        if ($this->connection->connect_error) {
            throw new \RuntimeException(
                "Conexión fallida: " . $this->connection->connect_error
            );
        }
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): mysqli {
        return $this->connection;
    }
    
    public function prepare(string $sql): \mysqli_stmt {
        return $this->connection->prepare($sql);
    }
    
    public function query(string $sql): \mysqli_result {
        return $this->connection->query($sql);
    }
    
    public function close(): void {
        $this->connection->close();
    }
}