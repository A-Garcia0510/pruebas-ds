<?php
// src/MVC/Routes/Router.php
namespace App\MVC\Routes;

use App\MVC\Controllers\BaseController;

class Router
{
    private $routes = [];
    private $notFoundCallback;

    /**
     * Añade una ruta GET al router
     * 
     * @param string $path
     * @param callable|array $callback
     */
    public function get(string $path, $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Añade una ruta POST al router
     * 
     * @param string $path
     * @param callable|array $callback
     */
    public function post(string $path, $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Añade una ruta al array de rutas
     * 
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     */
    private function addRoute(string $method, string $path, $callback): void
    {
        // Normalizar la ruta para que siempre comience con '/'
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        $this->routes[$method][$path] = $callback;
    }

    /**
     * Define el callback para rutas no encontradas
     * 
     * @param callable $callback
     */
    public function setNotFoundHandler($callback): void
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * Resuelve la ruta actual y ejecuta el controlador correspondiente
     */
    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        // Obtenemos la ruta desde la URL
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Buscar en las rutas definidas
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            $this->executeCallback($callback);
            return;
        }

        // Si llegamos aquí, la ruta no existe
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
            return;
        }

        // Fallback si no hay controlador para rutas no encontradas
        header("HTTP/1.0 404 Not Found");
        echo '<h1>404 - Página no encontrada</h1>';
    }

    /**
     * Ejecuta el callback asociado a una ruta
     * 
     * @param callable|array $callback
     */
    private function executeCallback($callback): void
    {
        if (is_callable($callback)) {
            call_user_func($callback);
        } elseif (is_array($callback) && count($callback) === 2) {
            [$controller, $method] = $callback;
            
            if (is_string($controller)) {
                $controller = new $controller();
            }
            
            if ($controller instanceof BaseController) {
                call_user_func([$controller, $method]);
            }
        }
    }
}