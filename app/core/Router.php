<?php
namespace App\Core;

/**
 * Gestiona las rutas de la aplicación
 */
class Router
{
    protected $routes = [];
    protected $notFoundHandler;
    protected $request;
    protected $response;
    protected $container;
    
    /**
     * Constructor del Router
     * 
     * @param Request $request
     * @param Response $response
     * @param Container $container
     */
    public function __construct(Request $request, Response $response, Container $container)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;

        // Definir rutas de Custom Coffee
        $this->get('/custom-coffee', [\App\Controllers\CustomCoffeeController::class, 'index']);
        $this->get('/custom-coffee/builder', [\App\Controllers\CustomCoffeeController::class, 'index']);
        $this->get('/custom-coffee/recipes', [\App\Controllers\CustomCoffeeController::class, 'recipes']);
        $this->get('/custom-coffee/orders', [\App\Controllers\CustomCoffeeController::class, 'orders']);
        $this->get('/custom-coffee/order-details/:id', [\App\Controllers\CustomCoffeeController::class, 'orderDetails']);
        
        // API endpoints - Usar array para mejor manejo de dependencias
        $this->post('/api/custom-coffee/place-order', [\App\Controllers\CustomCoffeeController::class, 'placeOrder']);
        $this->get('/api/custom-coffee/get-components', [\App\Controllers\CustomCoffeeController::class, 'getComponentes']);
        $this->post('/api/custom-coffee/save-recipe', [\App\Controllers\CustomCoffeeController::class, 'saveRecipe']);
        $this->post('/api/custom-coffee/delete-recipe/:id', [\App\Controllers\CustomCoffeeController::class, 'deleteRecipe']);
        $this->post('/api/custom-coffee/order/:id/cancel', [\App\Controllers\CustomCoffeeController::class, 'cancelOrder']);
    }
    
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
     * @return mixed
     */
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        
        // Normalizar path para quitar o añadir slash final según sea necesario
        $path = $this->normalizePath($path);
        
        // Debug log
        error_log("Router::resolve() - Iniciando resolución de ruta");
        error_log("Router::resolve() - Método: $method");
        error_log("Router::resolve() - Path: $path");
        error_log("Router::resolve() - Rutas disponibles: " . print_r($this->routes[$method] ?? [], true));
        
        // Revisar rutas exactas primero
        if (isset($this->routes[$method][$path])) {
            error_log("Router::resolve() - Ruta exacta encontrada: $path");
            $callback = $this->routes[$method][$path];
            return $this->executeCallback($callback, []);
        }
        
        // Revisar rutas con parámetros
        foreach ($this->routes[$method] as $route => $callback) {
            // Verificar si la ruta tiene parámetros (ya sea :id o {id})
            if (strpos($route, ':') !== false || strpos($route, '{') !== false) {
                error_log("Router::resolve() - Evaluando ruta con parámetros: $route");
                $routeRegex = $this->convertRouteToRegex($route);
                error_log("Router::resolve() - Regex generado: $routeRegex");
                
                if (preg_match($routeRegex, $path, $matches)) {
                    error_log("Router::resolve() - Coincidencia encontrada para: $route");
                    // Extraer parámetros
                    $params = $this->extractParams($route, $matches);
                    error_log("Router::resolve() - Parámetros extraídos: " . print_r($params, true));
                    
                    return $this->executeCallback($callback, $params);
                } else {
                    error_log("Router::resolve() - No hubo coincidencia para: $route");
                }
            }
        }
        
        error_log("Router::resolve() - No se encontró ninguna ruta coincidente");
        // Intentar con o sin slash final
        $altPath = ($path === '/') ? $path : rtrim($path, '/');
            
        if ($path !== $altPath && isset($this->routes[$method][$altPath])) {
            $callback = $this->routes[$method][$altPath];
            return $this->executeCallback($callback, []);
        } 
        // Intentar con slash final si no tiene
        else if ($path !== "$altPath/" && isset($this->routes[$method]["$altPath/"])) {
            $callback = $this->routes[$method]["$altPath/"];
            return $this->executeCallback($callback, []);
        }
        
        // No se encontró la ruta
        if ($this->notFoundHandler) {
            return call_user_func($this->notFoundHandler, $this->request, $this->response);
        }
        
        $this->response->setStatusCode(404);
        return $this->renderNotFoundPage();
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
        
        // Reemplazar parámetros con patrones regex (tanto :id como {id})
        $route = preg_replace('/\\\:([a-zA-Z0-9_]+)|\\\{([a-zA-Z0-9_]+)\\\}/', '([^\/]+)', $route);
        
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
        // Buscar tanto :id como {id}
        preg_match_all('/[:\{]([a-zA-Z0-9_]+)[\}]?/', $route, $paramNames);
        
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
     * @param array $params
     * @return mixed
     */
    protected function executeCallback($callback, $params)
    {
        if (is_array($callback)) {
            [$controllerClass, $method] = $callback;
            
            // Log para depuración
            error_log("Router::executeCallback() - Ejecutando: $controllerClass::$method");
            error_log("Router::executeCallback() - Parámetros: " . print_r($params, true));
            error_log("Router::executeCallback() - Método HTTP: " . $this->request->getMethod());
            error_log("Router::executeCallback() - Headers: " . print_r($this->request->getHeaders(), true));
            
            // Usar el contenedor para crear el controlador
            $controller = $this->container->resolve($controllerClass);
            
            // Obtener los parámetros del método usando reflexión
            $reflection = new \ReflectionMethod($controller, $method);
            $methodParams = $reflection->getParameters();
            
            // Preparar los argumentos en el orden correcto
            $args = [];
            foreach ($methodParams as $param) {
                $paramName = $param->getName();
                if (isset($params[$paramName])) {
                    $args[] = $params[$paramName];
                } else if ($param->isOptional()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    error_log("Router::executeCallback() - Error: Parámetro requerido no proporcionado: $paramName");
                    throw new \Exception("Parámetro requerido no proporcionado: $paramName");
                }
            }
            
            error_log("Router::executeCallback() - Argumentos finales: " . print_r($args, true));
            return $reflection->invokeArgs($controller, $args);
        }
        
        return call_user_func_array($callback, [$this->request, $this->response]);
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
     * @return string
     */
    protected function renderNotFoundPage()
    {
        $this->response->setStatusCode(404);
        
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