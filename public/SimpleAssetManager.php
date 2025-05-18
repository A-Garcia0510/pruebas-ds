<?php
/**
 * SimpleAssetManager.php
 * 
 * Una clase simple y directa para gestionar assets (CSS, JS, imágenes)
 * sin depender de autoloading o estructura de clases compleja.
 */

class SimpleAssetManager {
    /**
     * Obtiene la URL base para los activos
     * 
     * @return string URL base
     */
    public static function getBaseUrl() {
        // Detectar protocolo
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        // Obtener el host
        $host = $_SERVER['HTTP_HOST'];
        
        // Obtener la ruta base de la aplicación
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = rtrim(dirname($scriptName), '/');
        
        // Si estamos en el directorio raíz, basePath será una cadena vacía
        return "$protocol://$host$basePath";
    }
    
    /**
     * Obtiene la URL completa para un archivo CSS
     * 
     * @param string $filename Nombre del archivo CSS sin extensión
     * @return string URL completa al archivo CSS
     */
    public static function css($filename) {
        return self::getBaseUrl() . '/css/' . $filename . '.css';
    }
    
    /**
     * Obtiene la URL completa para un archivo JavaScript
     * 
     * @param string $filename Nombre del archivo JS sin extensión
     * @return string URL completa al archivo JS
     */
    public static function js($filename) {
        return self::getBaseUrl() . '/js/' . $filename . '.js';
    }
    
    /**
     * Obtiene la URL completa para una imagen
     * 
     * @param string $path Ruta de la imagen relativa a la carpeta /img
     * @return string URL completa a la imagen
     */
    public static function img($path) {
        return self::getBaseUrl() . '/img/' . $path;
    }
    
    /**
     * Obtiene la URL completa para una ruta específica
     * 
     * @param string $path Ruta relativa a la raíz del proyecto
     * @return string URL completa
     */
    public static function url($path = '') {
        $path = ltrim($path, '/');
        return self::getBaseUrl() . ($path ? "/$path" : '');
    }
}