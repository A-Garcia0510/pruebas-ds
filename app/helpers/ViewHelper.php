<?php
/**
 * ViewHelper.php
 * 
 * Clase para ayudar en la renderización de vistas
 */

class ViewHelper {
    /**
     * Renderiza un componente parcial de la vista
     * 
     * @param string $partial Nombre del archivo parcial
     * @param array $data Datos para pasar al parcial
     * @return string HTML del parcial renderizado
     */
    public static function renderPartial($partial, $data = []) {
        // Extraer variables para que estén disponibles en el parcial
        extract($data);
        
        $partialPath = BASE_PATH . '/app/views/partials/' . $partial . '.php';
        
        if (!file_exists($partialPath)) {
            error_log("Error: Parcial '$partial' no encontrado en $partialPath");
            return "<!-- Error: Parcial '$partial' no encontrado -->";
        }
        
        // Capturar la salida del parcial
        ob_start();
        include $partialPath;
        return ob_get_clean();
    }
    
    /**
     * Determina si el usuario está autenticado
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}