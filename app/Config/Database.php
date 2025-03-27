<?php
namespace Config;

use mysqli;

class Database {
    private static $instance = null;
    private $conn;
    
    private $host = "mysql.inf.uct.cl";
    private $user = "agarcia";
    private $password = "chuMKZ3EdhJvje706";
    private $database = "A2024_agarcia";
    
    private function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            throw new \Exception("Conexión fallida: " . $this->conn->connect_error);
        }
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): mysqli {
        return $this->conn;
    }
}