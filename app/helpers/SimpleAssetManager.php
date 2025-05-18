<?php
namespace App\Helpers;

/**
 * Gestor de activos simplificado para manejar CSS, JS e imágenes
 */
class SimpleAssetManager
{
    /**
     * Obtiene la URL relativa para un archivo CSS
     * 
     * @param string $filename Nombre del archivo CSS sin extensión
     * @return string URL relativa al archivo CSS
     */
    public static function css($filename)
    {
        return '/pruebas-ds/public/css/' . $filename . '.css';
    }
    
    /**
     * Obtiene la URL relativa para un archivo JavaScript
     * 
     * @param string $filename Nombre del archivo JS sin extensión
     * @return string URL relativa al archivo JS
     */
    public static function js($filename)
    {
        return '/pruebas-ds/public/js/' . $filename . '.js';
    }
    
    /**
     * Obtiene la URL relativa para una imagen
     * 
     * @param string $path Ruta de la imagen relativa a la carpeta /img
     * @return string URL relativa a la imagen
     */
    public static function img($path)
    {
        return '/pruebas-ds/public/img/' . $path;
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
        return empty($path) ? '/pruebas-ds/public/' : '/pruebas-ds/public/' . $path;
    }
}