<?php
namespace App\Helpers;

/**
 * Clase auxiliar mejorada y corregida para manejar rutas de activos (CSS, JS, imágenes)
 */
class AssetHelper
{
    private static $baseUrl;
    
    /**
     * Inicializa el helper con la URL base
     * 
     * @param array $config Configuración de la aplicación
     */
    public static function init($config)
    {
        if (isset($config['app']['url']) && !empty($config['app']['url'])) {
            // Usar la URL configurada si está definida y no está vacía
            self::$baseUrl = rtrim($config['app']['url'], '/');
        } else {
            // Detectar automáticamente la URL base si no está configurada o está vacía
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            
            // Determinar la ruta base examinando SCRIPT_NAME
            $scriptName = $_SERVER['SCRIPT_NAME']; // Ejemplo: /pruebas-ds/public/index.php
            $dirPath = dirname($scriptName); // Ejemplo: /pruebas-ds/public
            
            // Normalizar el directorio para que no termine en barra diagonal
            // Pero preservarlo si es la raíz
            if ($dirPath === '/' || $dirPath === '\\') {
                $dirPath = '';
            }
            
            self::$baseUrl = $protocol . $host . $dirPath;
        }
        
        // Log para depuración
        if (isset($config['app']['debug']) && $config['app']['debug']) {
            error_log('AssetHelper::init() - BaseUrl configurada como: ' . self::$baseUrl);
            error_log('AssetHelper::init() - REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
            error_log('AssetHelper::init() - SCRIPT_NAME: ' . $_SERVER['SCRIPT_NAME']);
            error_log('AssetHelper::init() - DOCUMENT_ROOT: ' . $_SERVER['DOCUMENT_ROOT']);
        }
    }
    
    /**
     * Genera una URL para un archivo CSS
     * 
     * @param string $filename Nombre del archivo CSS sin extensión
     * @return string URL completa al archivo CSS
     */
    public static function css($filename)
    {
        return self::$baseUrl . '/css/' . $filename . '.css';
    }
    
    /**
     * Genera una URL para un archivo JavaScript
     * 
     * @param string $filename Nombre del archivo JS sin extensión
     * @return string URL completa al archivo JS
     */
    public static function js($filename)
    {
        return self::$baseUrl . '/js/' . $filename . '.js';
    }
    
    /**
     * Genera una URL para una imagen
     * 
     * @param string $path Ruta de la imagen relativa a la carpeta /img
     * @return string URL completa a la imagen
     */
    public static function img($path)
    {
        return self::$baseUrl . '/img/' . $path;
    }
    
    /**
     * Genera una URL para cualquier ruta
     * 
     * @param string $path Ruta relativa a la raíz del sitio
     * @return string URL completa
     */
    public static function url($path = '')
    {
        $path = ltrim($path, '/');
        return empty($path) ? self::$baseUrl : self::$baseUrl . '/' . $path;
    }
    
    /**
     * Obtiene la URL base configurada
     * 
     * @return string URL base
     */
    public static function getBaseUrl()
    {
        return self::$baseUrl;
    }
}