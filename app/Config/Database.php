<?php
namespace App\Config;

use PDO;
use mysqli;

class Database {
    private static ?self $instance = null;
    private array $config;

    private function __construct(array $config = []) {
        $this->config = $config + [
            'host' => 'mysql.inf.uct.cl',
            'user' => 'agarcia',
            'password' => 'chuMKZ3EdhJvje706',
            'database' => 'A2024_agarcia',
            'driver' => 'mysql'
        ];
    }

    public static function getInstance(array $config = []): self {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getMySQLConnection(): mysqli {
        $connection = new mysqli(
            $this->config['host'], 
            $this->config['user'], 
            $this->config['password'], 
            $this->config['database']
        );
        
        if ($connection->connect_error) {
            throw new \RuntimeException(
                "Conexión fallida: " . $connection->connect_error
            );
        }
        
        return $connection;
    }

    public function getPDOConnection(): PDO {
        try {
            $dsn = "{$this->config['driver']}:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            return new PDO($dsn, $this->config['user'], $this->config['password'], $options);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Error de conexión: " . $e->getMessage());
        }
    }

    public function close(): void {
        // Método para cerrar conexiones si es necesario
    }
}