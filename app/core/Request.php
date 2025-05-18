<?php
namespace App\Core;

/**
 * Gestiona los datos de la solicitud HTTP
 */
class Request
{
    /**
     * Obtiene la ruta actual de la URL
     * 
     * @return string
     */
    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Si estamos en un subdirectorio, necesitamos ajustar la ruta
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        
        // Si no estamos en el directorio raíz y el path comienza con ese directorio
        if ($scriptDir !== '/' && $scriptDir !== '\\' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }
        
        // Eliminar parámetros de consulta si existen
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        // Asegurarse de que el path comience con /
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // Depuración
        if (App::$app->config['app']['debug'] ?? false) {
            error_log("RequestURI: " . $_SERVER['REQUEST_URI']);
            error_log("ScriptName: " . $scriptName);
            error_log("ScriptDir: " . $scriptDir);
            error_log("Path procesado: " . $path);
        }
        
        return $path;
    }
    
    /**
     * Obtiene el método HTTP actual
     * 
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }
    
    /**
     * Determina si la petición es GET
     * 
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }
    
    /**
     * Determina si la petición es POST
     * 
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }
    
    /**
     * Obtiene los datos del cuerpo (para peticiones POST)
     * 
     * @return array
     */
    public function getBody()
    {
        $body = [];
        
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        
        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        
        return $body;
    }
    
    /**
     * Obtiene un valor específico de la solicitud
     * 
     * @param string $key Nombre del parámetro
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }
}