<?php
require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($correo, $contraseña) {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM Usuario WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if ($contraseña === $user['contraseña']) {
                // Login exitoso, guardar información en sesión
                $_SESSION['correo'] = $correo;
                $_SESSION['mensaje'] = "Inicio de sesión exitoso.";
                return true;
            }
        }
        
        return false;
    }
    
    public function register($nombre, $apellidos, $correo, $contraseña) {
        if (strlen($contraseña) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }
        
        // Verificar si el correo ya está registrado
        if ($this->emailExists($correo)) {
            return ['success' => false, 'message' => 'El correo ya está registrado.'];
        }
        
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO Usuario (nombre, apellidos, correo, contraseña) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $apellidos, $correo, $contraseña);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registro exitoso. Puedes iniciar sesión ahora.'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar: ' . $stmt->error];
        }
    }
    
    public function emailExists($correo) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    
    public function getUserData($correo) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT nombre, apellidos FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function logout() {
        $_SESSION = [];
        session_destroy();
    }
}
?>