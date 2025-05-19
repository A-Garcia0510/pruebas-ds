<?php
// Modificar el Service SessionHandler.php para asegurar que la sesión se gestiona correctamente

namespace App\Auth\Services;

use App\Auth\Interfaces\SessionHandlerInterface;

class SessionHandler implements SessionHandlerInterface
{
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function set(string $key, $value): void
    {
        // Aseguramos que la sesión esté iniciada
        $this->startSession();
        $_SESSION[$key] = $value;
        
        // Si estamos guardando el ID de usuario, también lo guardamos con otra clave
        // para mantener compatibilidad con el resto del sistema
        if ($key === 'usuario_id') {
            $_SESSION['user_id'] = $value;
        }
        
        // Si estamos guardando el correo, también actualizamos email
        if ($key === 'correo') {
            $_SESSION['email'] = $value;
        }
    }
    
    public function get(string $key, $default = null)
    {
        $this->startSession();
        
        // Si buscamos user_id pero solo tenemos usuario_id
        if ($key === 'user_id' && !isset($_SESSION['user_id']) && isset($_SESSION['usuario_id'])) {
            return $_SESSION['usuario_id'];
        }
        
        // Si buscamos email pero solo tenemos correo
        if ($key === 'email' && !isset($_SESSION['email']) && isset($_SESSION['correo'])) {
            return $_SESSION['correo'];
        }
        
        return $this->has($key) ? $_SESSION[$key] : $default;
    }
    
    public function has(string $key): bool
    {
        $this->startSession();
        
        // Verificar claves alternativas también
        if ($key === 'user_id' && isset($_SESSION['usuario_id'])) {
            return true;
        }
        
        if ($key === 'email' && isset($_SESSION['correo'])) {
            return true;
        }
        
        return isset($_SESSION[$key]);
    }
    
    public function remove(string $key): void
    {
        $this->startSession();
        
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
        
        // Eliminar claves alternativas
        if ($key === 'user_id' && isset($_SESSION['usuario_id'])) {
            unset($_SESSION['usuario_id']);
        } else if ($key === 'usuario_id' && isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }
        
        if ($key === 'email' && isset($_SESSION['correo'])) {
            unset($_SESSION['correo']);
        } else if ($key === 'correo' && isset($_SESSION['email'])) {
            unset($_SESSION['email']);
        }
    }
    
    public function destroy(): void
    {
        $this->startSession();
        
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
}