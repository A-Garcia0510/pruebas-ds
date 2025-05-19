<?php
// src/Auth/Services/SessionHandler.php
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
        $_SESSION[$key] = $value;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $_SESSION[$key] : $default;
    }
    
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }
    
    public function destroy(): void
    {
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