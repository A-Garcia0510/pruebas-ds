<?php
// src/MVC/Routes/Router.php
namespace App\MVC\Routes;

class Router
{
    private $routes = [];
    
    /**
     * Añade una ruta GET
     * 
     * @param string $path Ruta URL
     * @param string|callable $handler Controlador@método o función anónima
     * @return Router
     */
    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Añade una ruta POST
     * 
     * @param string $path Ruta URL
     * @param string|callable $handler Controlador@método o función anónima
     * @return Router
     */
    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Añade una ruta para cualquier método HTTP
     * 
     * @param string $path Ruta URL
     * @param string|callable $handler Controlador@método o función anónima
     * @return Router
     */
    public function any(string $path, $handler): self
    {
        return $this->addRoute('ANY', $path, $handler);
    }
    
    /**
     * Añade una ruta al array de rutas
     * 
     * @param string $method Método HTTP
     * @param string $path Ruta URL
     * @param string|callable $handler Controlador@método o función anónima
     * @return Router
     */
    private function addRoute(string $method, string $path, $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
        
        return $this;
    }
    
    /**
     * Resuelve y ejecuta el manejador para la ruta actual
     * 
     * @return void
     */
    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Eliminar trailing slash
        $requestPath = rtrim($requestPath, '/');
        if (empty($requestPath)) {
            $requestPath = '/';
        }
        
        foreach ($this->routes as $route) {
            // Verificar si el método coincide (o es ANY)
            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                continue;
            }
            
            // Comprobar si la ruta coincide exactamente
            if ($route['path'] === $requestPath) {
                $this->executeHandler($route['handler']);
                return;
            }
            
            // TODO: Implementar rutas con parámetros (ej: /user/{id})
        }
        
        // Si no se encuentra la ruta
        http_response_code(404);
        echo "404 - Página no encontrada";
    }
    
    /**
     * Ejecuta el manejador de la ruta
     * 
     * @param string|callable $handler Controlador@método o función anónima
     * @return void
     */
    private function executeHandler($handler): void
    {
        if (is_callable($handler)) {
            // Si el manejador es una función anónima
            call_user_func($handler);
        } else if (is_string($handler)) {
            // Si el manejador es un string 'Controlador@método'
            [$controller, $method] = explode('@', $handler);
            
            // Si el controlador no incluye namespace, añadir el namespace predeterminado
            if (strpos($controller, '\\') === false) {
                $controller = 'App\\MVC\\Controllers\\' . $controller;
            }
            
            // Instanciar el controlador y llamar al método
            $controllerInstance = new $controller();
            if (!method_exists($controllerInstance, $method)) {
                throw new \Exception("El método {$method} no existe en el controlador {$controller}");
            }
            
            call_user_func([$controllerInstance, $method]);
        } else {
            throw new \Exception("Tipo de manejador no válido");
        }
    }
}