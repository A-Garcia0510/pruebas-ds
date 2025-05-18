<?php
namespace App\Helpers;

/**
 * Clase auxiliar para manejar rutas de activos (CSS, JS, imágenes)
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
        if (isset($config['app']['url'])) {
            // Eliminar slash final si existe
            $url = rtrim($config['app']['url'], '/');
            self::$baseUrl = $url;
        } else {
            // URL por defecto si no está configurada
            self::$baseUrl = '';
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
        return self::$baseUrl . '/' . $path;
    }
}