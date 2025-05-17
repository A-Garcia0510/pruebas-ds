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
        $position = strpos($path, '?');
        
        if ($position === false) {
            return $path;
        }
        
        return substr($path, 0, $position);
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