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
        
        // Normalizar path para quitar o añadir slash final según sea necesario
        $path = $this->normalizePath($path);
        
        // Revisar si la ruta existe
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
        } 
        // Intentar con o sin slash final
        else {
            $altPath = ($path === '/') ? $path : rtrim($path, '/');
            
            if ($path !== $altPath && isset($this->routes[$method][$altPath])) {
                $callback = $this->routes[$method][$altPath];
            } 
            // Intentar con slash final si no tiene
            else if ($path !== "$altPath/" && isset($this->routes[$method]["$altPath/"])) {
                $callback = $this->routes[$method]["$altPath/"];
            }
            else {
                // No se encontró la ruta
                if ($this->notFoundHandler) {
                    return call_user_func($this->notFoundHandler, $request, $response);
                }
                
                $response->setStatusCode(404);
                return $this->renderNotFoundPage($response);
            }
        }
        
        // Si el callback es un array [controlador, método]
        if (is_array($callback)) {
            $controllerClass = $callback[0];
            $controller = new $controllerClass($request, $response);
            $method = $callback[1];
            
            // Ejecutar el método del controlador
            return $controller->$method();
        }
        
        // Si el callback es una función
        return call_user_func($callback, $request, $response);
    }
    
    /**
     * Normaliza la ruta para manejar consistentemente con o sin slash final
     * 
     * @param string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        // Si es la raíz, devolver solo /
        if ($path === '/' || $path === '') {
            return '/';
        }
        
        // Eliminar múltiples slashes consecutivos
        $path = preg_replace('#/+#', '/', $path);
        
        // Asegurar que la ruta comience con /
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $path;
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