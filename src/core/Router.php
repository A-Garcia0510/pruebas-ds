<?php
/**
 * Router - Clase encargada de enrutar las peticiones HTTP a los controladores correspondientes
 */
class Router {
    // Variable para activar/desactivar mensajes de depuración
    private $debug = false;
    // Variable para almacenar la URL base del proyecto
    private $baseUrl;
    // Variable para almacenar las rutas personalizadas
    private $routes = [
        // Formato: 'ruta' => ['controlador', 'método']
        'servicios' => ['Pages', 'servicios'],
        'ayuda' => ['Pages', 'ayuda'],
        // Más rutas personalizadas aquí
    ];
    
    /**
     * Constructor de la clase Router
     */
    public function __construct() {
        if ($this->debug) {
            $this->debugLog("Inicializando Router");
        }
        
        // Determinar la URL base del proyecto
        $this->determineBaseUrl();
        
        if ($this->debug) {
            $this->debugLog("URL Base del proyecto: " . $this->baseUrl);
        }
    }
    
    /**
     * Método para determinar la URL base del proyecto
     */
    private function determineBaseUrl() {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $this->baseUrl = ($scriptDir == '/' || $scriptDir == '\\') ? '' : $scriptDir;
    }
    
    /**
     * Método para obtener la URL base del proyecto
     * 
     * @return string URL base del proyecto
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }
    
    /**
     * Método de depuración para mostrar mensajes
     * 
     * @param mixed $message Mensaje o datos para mostrar
     * @return void
     */
    private function debugLog($message) {
        if ($this->debug) {
            echo '<div style="background: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin: 5px; font-family: monospace;">';
            echo "Debug: ";
            if (is_array($message) || is_object($message)) {
                echo '<pre>';
                print_r($message);
                echo '</pre>';
            } else {
                echo $message;
            }
            echo '</div>';
        }
    }
    
    /**
     * Método para analizar la URL y dirigir la solicitud al controlador adecuado
     */
    public function dispatch() {
        // Obtener la URL solicitada
        $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        
        // Eliminar parámetros GET de la URL
        $requestUrl = strtok($requestUrl, '?');
        
        // Eliminar la parte base de la URL
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if($basePath != '/' && $basePath != '\\') {
            $requestUrl = substr($requestUrl, strlen($basePath));
        }
        
        $this->debugLog("URL solicitada: " . $requestUrl);
        $this->debugLog("Base Path: " . $basePath);
        
        // Si la URL termina con index.php o es similar a la raíz, cargar la página de inicio
        if(empty($requestUrl) || $requestUrl == '/' || $requestUrl == '/index.php' || $requestUrl == 'index.php') {
            $this->debugLog("Cargando página de inicio...");
            
            // Buscar HomeController en múltiples ubicaciones posibles
            $controllerPaths = [
                __DIR__ . '/Controller/HomeController.php',
                dirname(__DIR__) . '/core/Controller/HomeController.php',
                dirname(dirname(__DIR__)) . '/core/Controller/HomeController.php',
                $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/src/core/Controller/HomeController.php'
            ];
            
            $controllerFound = false;
            
            foreach ($controllerPaths as $path) {
                $this->debugLog("Buscando controlador en: " . $path);
                
                if(file_exists($path)) {
                    $this->debugLog("¡Controlador encontrado! Cargando: " . $path);
                    require_once $path;
                    
                    if(class_exists('HomeController')) {
                        $controller = new HomeController();
                        // Pasar la instancia del router al controlador
                        if (method_exists($controller, 'setRouter')) {
                            $controller->setRouter($this);
                        }
                        if(method_exists($controller, 'index')) {
                            $controller->index();
                            $controllerFound = true;
                            break;
                        } else {
                            $this->debugLog("Error: El método 'index' no existe en HomeController");
                        }
                    } else {
                        $this->debugLog("Error: La clase 'HomeController' no está definida en el archivo");
                    }
                }
            }
            
            if (!$controllerFound) {
                $this->debugLog("No se pudo encontrar el controlador. Mostrando página de inicio temporal.");
                $this->showFallbackHome();
            }
            
            return;
        }
        
        // Verificar si hay una ruta personalizada definida
        $trimmedUrl = trim($requestUrl, '/');
        
        if (isset($this->routes[$trimmedUrl])) {
            $route = $this->routes[$trimmedUrl];
            $controllerName = $route[0] . 'Controller';
            $action = $route[1];
            
            $this->debugLog("Ruta personalizada encontrada: $controllerName -> $action");
            
            // Buscar el controlador en múltiples ubicaciones posibles
            $controllerPaths = [
                __DIR__ . '/Controller/' . $controllerName . '.php',
                dirname(__DIR__) . '/core/Controller/' . $controllerName . '.php',
                dirname(dirname(__DIR__)) . '/core/Controller/' . $controllerName . '.php',
                $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/src/core/Controller/' . $controllerName . '.php'
            ];
            
            $controllerFound = false;
            
            foreach ($controllerPaths as $controllerFile) {
                $this->debugLog("Buscando controlador: " . $controllerFile);
                
                if(file_exists($controllerFile)) {
                    $this->debugLog("Controlador encontrado: " . $controllerFile);
                    require_once $controllerFile;
                    
                    if(class_exists($controllerName)) {
                        $controller = new $controllerName();
                        
                        // Pasar la instancia del router al controlador
                        if (method_exists($controller, 'setRouter')) {
                            $controller->setRouter($this);
                        }
                        
                        // Verificar si el método existe
                        if(method_exists($controller, $action)) {
                            // Llamar al método del controlador
                            call_user_func([$controller, $action]);
                            $controllerFound = true;
                            break;
                        } else {
                            $this->debugLog("Error: El método '$action' no existe en $controllerName");
                        }
                    } else {
                        $this->debugLog("Error: La clase '$controllerName' no está definida en el archivo");
                    }
                }
            }
            
            if ($controllerFound) {
                return;
            }
        }
        
        // Continuar con el enrutamiento normal si no hay ruta personalizada
        // Dividir la URL en segmentos
        $segments = explode('/', trim($requestUrl, '/'));
        
        // El primer segmento es el controlador
        $controllerName = ucfirst(strtolower($segments[0])) . 'Controller';
        
        // Buscar el controlador en múltiples ubicaciones posibles
        $controllerPaths = [
            __DIR__ . '/Controller/' . $controllerName . '.php',
            dirname(__DIR__) . '/core/Controller/' . $controllerName . '.php',
            dirname(dirname(__DIR__)) . '/core/Controller/' . $controllerName . '.php',
            $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/src/core/Controller/' . $controllerName . '.php'
        ];
        
        $controllerFound = false;
        
        foreach ($controllerPaths as $controllerFile) {
            $this->debugLog("Buscando controlador: " . $controllerFile);
            
            if(file_exists($controllerFile)) {
                $this->debugLog("Controlador encontrado: " . $controllerFile);
                require_once $controllerFile;
                
                if(class_exists($controllerName)) {
                    $controller = new $controllerName();
                    
                    // Pasar la instancia del router al controlador
                    if (method_exists($controller, 'setRouter')) {
                        $controller->setRouter($this);
                    }
                    
                    // El segundo segmento es la acción (método) 
                    $action = isset($segments[1]) ? strtolower($segments[1]) : 'index';
                    
                    // Verificar si el método existe
                    if(method_exists($controller, $action)) {
                        // Llamar al método del controlador
                        call_user_func([$controller, $action]);
                        $controllerFound = true;
                        break;
                    } else {
                        $this->debugLog("Error: El método '$action' no existe en $controllerName");
                    }
                } else {
                    $this->debugLog("Error: La clase '$controllerName' no está definida en el archivo");
                }
            }
        }
        
        if (!$controllerFound) {
            // Controlador o método no encontrado
            $this->notFound();
        }
    }
    
    /**
     * Método para mostrar una página de inicio temporal
     */
    private function showFallbackHome() {
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ethos Coffe - Inicio</title>
            <link rel="stylesheet" type="text/css" href="' . $this->baseUrl . '/CSS/index.css">
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                }
                .container { 
                    max-width: 1200px; 
                    margin: 0 auto; 
                    padding: 20px;
                }
                .hero { 
                    background-color: #f5f5f5;
                    padding: 50px 20px;
                    text-align: center;
                    margin-bottom: 30px;
                }
                h1 { color: #5D4037; }
                .btn {
                    display: inline-block;
                    background-color: #795548;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .features {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    margin-bottom: 30px;
                }
                .feature {
                    flex: 0 0 30%;
                    margin-bottom: 20px;
                    padding: 20px;
                    background-color: #f9f9f9;
                    border-radius: 5px;
                }
                footer {
                    background-color: #333;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    margin-top: 40px;
                }
                @media (max-width: 768px) {
                    .feature {
                        flex: 0 0 100%;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="hero">
                    <h1>Bienvenido a Ethos Coffe</h1>
                    <p>Tu destino para una experiencia de café excepcional.</p>
                    <a href="' . $this->baseUrl . '/PHP/productos.php" class="btn">Ver Productos</a>
                </div>
                
                <div class="features">
                    <div class="feature">
                        <h3>Productos de Alta Calidad</h3>
                        <p>Ofrecemos una amplia gama de productos con descripciones detalladas para que conozcas exactamente lo que estás comprando.</p>
                    </div>
                    
                    <div class="feature">
                        <h3>Envío Rápido</h3>
                        <p>Entendemos que quieres recibir tus productos lo antes posible. Te proporcionamos estimaciones realistas de tiempo de entrega.</p>
                    </div>
                    
                    <div class="feature">
                        <h3>Atención al Cliente</h3>
                        <p>Nuestro equipo está disponible para resolver todas tus dudas y preocupaciones a través de correo electrónico o teléfono.</p>
                    </div>
                </div>
                
                <div>
                    <h2>Estamos trabajando en nuestro nuevo sitio</h2>
                    <p>Pronto tendremos disponible una experiencia de compra mejorada con nuestra nueva estructura MVC.</p>
                </div>
            </div>
            
            <footer>
                <p>&copy; ' . date('Y') . ' Ethos Coffe. Todos los derechos reservados.</p>
            </footer>
        </body>
        </html>';
    }
    
    /**
     * Método para manejar URLs no encontradas
     */
    private function notFound() {
        // Establecer el código de estado HTTP 404
        header("HTTP/1.0 404 Not Found");
        
        // Cargar una vista de error 404
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error 404 - Página no encontrada</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    text-align: center;
                }
                h1 { color: #d32f2f; }
                .error-container {
                    background-color: #f5f5f5;
                    border-radius: 5px;
                    padding: 20px;
                    margin: 30px 0;
                }
                .back-link {
                    display: inline-block;
                    background-color: #795548;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .debug-info {
                    margin-top: 30px;
                    text-align: left;
                    font-size: 14px;
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>Error 404 - Página no encontrada</h1>
                <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
                <a href="' . $this->baseUrl . '/" class="back-link">Volver a la página de inicio</a>
            </div>';
            
        if ($this->debug) {
            echo '<div class="debug-info">
                <h3>Información de depuración:</h3>
                <p><strong>URL solicitada:</strong> ' . $_SERVER['REQUEST_URI'] . '</p>
                <p><strong>Script actual:</strong> ' . $_SERVER['SCRIPT_NAME'] . '</p>
                <p><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</p>
                <p><strong>Base Path:</strong> ' . dirname($_SERVER['SCRIPT_NAME']) . '</p>
                <p><strong>Base URL:</strong> ' . $this->baseUrl . '</p>
            </div>';
        }
            
        echo '</body>
        </html>';
        exit;
    }
}