<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private $servidor = "mysql.inf.uct.cl";
    private $user = "agarcia";
    private $password = "chuMKZ3EdhJvje706";
    private $basedato = "A2024_agarcia";
    
    private function __construct() {
        $this->conn = new mysqli($this->servidor, $this->user, $this->password, $this->basedato);
        
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }
    
    // Patrón Singleton para evitar múltiples conexiones
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function close() {
        $this->conn->close();
    }
}
?>