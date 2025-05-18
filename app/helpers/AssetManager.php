<?php
namespace App\Helpers;

use App\Core\App;

/**
 * Gestor de activos mejorado para manejar CSS, JS e imágenes
 */
class AssetManager
{
    /**
     * Obtiene la URL base para los activos
     * 
     * @return string URL base
     */
    public static function getBaseUrl()
    {
        $config = App::$app->config;
        
        // Si hay una URL base definida en la configuración, usarla
        if (isset($config['assets']['base_url']) && !empty($config['assets']['base_url'])) {
            return rtrim($config['assets']['base_url'], '/');
        }
        
        // Detectar automáticamente
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = dirname($scriptName);
        
        // Si estamos en la raíz, devolver una barra
        if ($scriptDir == '/' || $scriptDir == '\\') {
            return '';
        }
        
        return $scriptDir;
    }
    
    /**
     * Obtiene la URL relativa para un archivo CSS
     * 
     * @param string $filename Nombre del archivo CSS sin extensión
     * @return string URL relativa al archivo CSS
     */
    public static function css($filename)
    {
        $config = App::$app->config;
        $cssPath = isset($config['assets']['css_path']) ? $config['assets']['css_path'] : '/css';
        
        return self::getBaseUrl() . $cssPath . '/' . $filename . '.css';
    }
    
    /**
     * Obtiene la URL relativa para un archivo JavaScript
     * 
     * @param string $filename Nombre del archivo JS sin extensión
     * @return string URL relativa al archivo JS
     */
    public static function js($filename)
    {
        $config = App::$app->config;
        $jsPath = isset($config['assets']['js_path']) ? $config['assets']['js_path'] : '/js';
        
        return self::getBaseUrl() . $jsPath . '/' . $filename . '.js';
    }
    
    /**
     * Obtiene la URL relativa para una imagen
     * 
     * @param string $path Ruta de la imagen relativa a la carpeta /img
     * @return string URL relativa a la imagen
     */
    public static function img($path)
    {
        $config = App::$app->config;
        $imgPath = isset($config['assets']['img_path']) ? $config['assets']['img_path'] : '/img';
        
        return self::getBaseUrl() . $imgPath . '/' . $path;
    }
    
    /**
     * Obtiene la URL relativa para una ruta específica
     * 
     * @param string $path Ruta relativa a la raíz del proyecto
     * @return string URL relativa completa
     */
    public static function url($path = '')
    {
        $path = ltrim($path, '/');
        return self::getBaseUrl() . '/' . $path;
    }
}