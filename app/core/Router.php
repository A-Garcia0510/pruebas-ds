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
        
        // Debug log
        if (App::$app->config['app']['debug'] ?? false) {
            error_log("Router::resolve() - Resolviendo ruta: $method $path");
        }
        
        // Revisar rutas exactas primero
        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];
            return $this->executeCallback($callback, $request, $response, []);
        }
        
        // Revisar rutas con parámetros
        foreach ($this->routes[$method] as $route => $callback) {
            if (strpos($route, '{') !== false) {
                $routeRegex = $this->convertRouteToRegex($route);
                
                if (preg_match($routeRegex, $path, $matches)) {
                    // Extraer parámetros
                    $params = $this->extractParams($route, $matches);
                    
                    // Log para depuración
                    if (App::$app->config['app']['debug'] ?? false) {
                        error_log("Router::resolve() - Ruta con parámetros encontrada: $route");
                        error_log("Router::resolve() - Parámetros: " . print_r($params, true));
                    }
                    
                    return $this->executeCallback($callback, $request, $response, $params);
                }
            }
        }
        
        // Intentar con o sin slash final
        $altPath = ($path === '/') ? $path : rtrim($path, '/');
            
        if ($path !== $altPath && isset($this->routes[$method][$altPath])) {
            $callback = $this->routes[$method][$altPath];
            return $this->executeCallback($callback, $request, $response, []);
        } 
        // Intentar con slash final si no tiene
        else if ($path !== "$altPath/" && isset($this->routes[$method]["$altPath/"])) {
            $callback = $this->routes[$method]["$altPath/"];
            return $this->executeCallback($callback, $request, $response, []);
        }
        
        // No se encontró la ruta
        if ($this->notFoundHandler) {
            return call_user_func($this->notFoundHandler, $request, $response);
        }
        
        $response->setStatusCode(404);
        return $this->renderNotFoundPage($response);
    }
    
    /**
     * Convierte una ruta con parámetros a expresión regular
     * 
     * @param string $route
     * @return string
     */
    protected function convertRouteToRegex($route)
    {
        // Escapar caracteres especiales
        $route = preg_quote($route, '/');
        
        // Reemplazar parámetros con patrones regex
        $route = preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '([^\/]+)', $route);
        
        return '/^' . $route . '$/';
    }
    
    /**
     * Extrae los parámetros de la URL
     * 
     * @param string $route
     * @param array $matches
     * @return array
     */
    protected function extractParams($route, $matches)
    {
        $params = [];
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $paramNames);
        
        // El primer elemento de $matches es la coincidencia completa
        array_shift($matches);
        
        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index] ?? null;
        }
        
        return $params;
    }
    
    /**
     * Ejecuta el callback con los parámetros extraídos
     * 
     * @param mixed $callback
     * @param Request $request
     * @param Response $response
     * @param array $params
     * @return mixed
     */
    protected function executeCallback($callback, $request, $response, $params)
    {
        if (is_array($callback)) {
            [$controllerClass, $method] = $callback;
            $controller = new $controllerClass($request, $response);
            
            // Log para depuración
            if (App::$app->config['app']['debug'] ?? false) {
                error_log("Router::executeCallback() - Ejecutando método: $controllerClass::$method");
                error_log("Router::executeCallback() - Parámetros: " . print_r($params, true));
            }
            
            if (empty($params)) {
                return $controller->$method();
            } else {
                return call_user_func_array([$controller, $method], $params);
            }
        }
        
        return call_user_func_array($callback, [$request, $response]);
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