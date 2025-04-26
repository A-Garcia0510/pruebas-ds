<?php
// app/Core/Router.php
namespace App\Core;

class Router
{
    protected $routes = [];
    protected $params = [];
    
    /**
     * Añade una ruta al enrutador
     * 
     * @param string $route La URL de la ruta
     * @param array $params Los parámetros de la ruta (controlador, acción, etc.)
     * @return void
     */
    public function add($route, $params = [])
    {
        // Convertir la ruta a una expresión regular
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        
        $this->routes[$route] = $params;
    }
    
    /**
     * Encuentra una ruta que coincida con la URL dada
     * 
     * @param string $url La URL a buscar
     * @return boolean
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                
                // Obtener los parámetros nombrados
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                
                $this->params = $params;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Despacha al controlador y acción correspondiente
     * 
     * @param string $url La URL a despachar
     * @return void
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);
        
        if ($this->match($url)) {
            $controller = $this->getNamespace() . $this->params['controller'];
            
            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);
                
                $action = $this->params['action'];
                
                if (method_exists($controller_object, $action)) {
                    $controller_object->$action();
                } else {
                    throw new \Exception("Método $action no encontrado en el controlador $controller");
                }
            } else {
                throw new \Exception("Controlador $controller no encontrado");
            }
        } else {
            // Manejar 404
            header("HTTP/1.0 404 Not Found");
            include(dirname(__DIR__) . '/Views/404.php');
        }
    }
    
    /**
     * Obtiene el namespace del controlador
     * 
     * @return string
     */
    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\';
        
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        
        return $namespace;
    }
    
    /**
     * Remueve las variables de la cadena de consulta
     * 
     * @param string $url La URL completa
     * @return string
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);
            
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        
        return $url;
    }
    
    /**
     * Obtiene los parámetros de la ruta
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}