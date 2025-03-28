<?php
namespace App\Security;

class SessionManager {
    /**
     * Iniciar sesión
     * @param int $userId ID del usuario
     */
    public function start(int $userId): void {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['last_activity'] = time();
        
        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);
    }

    /**
     * Cerrar sesión
     */
    public function destroy(): void {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Eliminar todas las variables de sesión
        $_SESSION = [];
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
    }

    /**
     * Verificar si hay una sesión activa
     * @return bool
     */
    public function isActive(): bool {
        return isset($_SESSION['user_id']) && 
               (time() - $_SESSION['last_activity'] <= 1800); // Timeout de 30 minutos
    }

    /**
     * Obtener el ID del usuario actual
     * @return int|null
     */
    public function getUserId(): ?int {
        return $this->isActive() ? $_SESSION['user_id'] : null;
    }
}