<?php
/**
 * AssetHelper.php
 * 
 * Un helper simplificado para manejar assets sin dependencias complejas
 */

class AssetHelper {
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
     * Obtiene la URL base para los assets (JS/CSS) de forma robusta
     */
    public static function assetBaseUrl() {
        $baseUrl = self::getBaseUrl();
        // Si termina en /public, no agregar nada
        if (preg_match('#/public$#', $baseUrl)) {
            return $baseUrl;
        }
        // Si termina en /pruebas-ds, agregar /public
        if (preg_match('#/pruebas-ds$#', $baseUrl)) {
            return $baseUrl . '/public';
        }
        // Si no contiene /pruebas-ds, agregarlo todo
        if (strpos($baseUrl, '/pruebas-ds') === false) {
            return rtrim($baseUrl, '/') . '/pruebas-ds/public';
        }
        // Si contiene /pruebas-ds pero no termina en /pruebas-ds ni /public
        return $baseUrl . '/public';
    }
    
    /**
     * Obtiene la URL completa para un archivo CSS
     * 
     * @param string $filename Nombre del archivo CSS sin extensión
     * @return string URL completa al archivo CSS
     */
    public static function css($filename) {
        return self::assetBaseUrl() . '/css/' . $filename . '.css?' . time();
    }
    
    /**
     * Obtiene la URL completa para un archivo JavaScript
     * 
     * @param string $filename Nombre del archivo JS sin extensión
     * @return string URL completa al archivo JS
     */
    public static function js($filename) {
        return self::assetBaseUrl() . '/js/' . $filename . '.js?' . time();
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
        $baseUrl = self::getBaseUrl();
        
        // Si estamos en el directorio public, no agregar /pruebas-ds/public
        if (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false) {
            return $baseUrl . ($path ? "/$path" : '');
        }
        
        // Si no estamos en public, agregar /pruebas-ds/public
        if (strpos($baseUrl, '/pruebas-ds/public') === false) {
            $baseUrl = rtrim($baseUrl, '/') . '/pruebas-ds/public';
        }
        
        return $baseUrl . ($path ? "/$path" : '');
    }
}