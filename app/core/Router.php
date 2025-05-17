<?php
namespace App\Core;

/**
 * Gestiona las rutas de la aplicación
 */
class Router
{
    protected $routes = [];
    protected $notFoundHandler;
    
    /**
     * Añade una ruta GET
     * 
     * @param string $path Ruta URL
     * @param array $callback [controlador, método]
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * Añade una ruta POST
     * 
     * @param string $path Ruta URL
     * @param array $callback [controlador, método]
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * Establece el manejador para rutas no encontradas
     * 
     * @param callable $handler Función que maneja rutas no encontradas
     */
    public function setNotFoundHandler($handler)
    {
        $this->notFoundHandler = $handler;
    }
    
    /**
     * Resuelve la ruta actual y ejecuta el callback correspondiente
     * 
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function resolve(Request $request, Response $response)
    {
        $path = $request->getPath();
        $method = $request->getMethod();
        $callback = $this->routes[$method][$path] ?? null;
        
        // Si no se encuentra la ruta
        if (!$callback) {
            if ($this->notFoundHandler) {
                return call_user_func($this->notFoundHandler, $request, $response);
            }
            
            $response->setStatusCode(404);
            return $this->renderNotFoundPage($response);
        }
        
        // Si el callback es un array [controlador, método]
        if (is_array($callback)) {
            $controller = new $callback[0]($request, $response);
            $callback[0] = $controller;
        }
        
        return call_user_func($callback, $request, $response);
    }
    
    /**
     * Renderiza la página 404 por defecto
     * 
     * @param Response $response
     * @return string
     */
    protected function renderNotFoundPage(Response $response)
    {
        $response->setStatusCode(404);
        
        // Comprobar si existe la vista 404
        $errorViewPath = dirname(__DIR__) . '/views/errors/404.php';
        
        if (file_exists($errorViewPath)) {
            ob_start();
            include_once $errorViewPath;
            return ob_get_clean();
        }
        
        return '<h1>404 - Página no encontrada</h1>';
    }
}