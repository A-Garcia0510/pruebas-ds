<?php
// app/Core/App.php
namespace App\Core;

class App
{
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * @var string
     */
    protected $requestUrl;
    
    /**
     * Constructor de la aplicación
     */
    public function __construct()
    {
        $this->router = new Router();
        $this->requestUrl = $this->processRequestUrl();
        
        $this->registerRoutes();
    }
    
    /**
     * Procesa la URL de la petición
     * 
     * @return string
     */
    protected function processRequestUrl()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Obtener la ruta base de la aplicación
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $baseDirectory = dirname($scriptName);
        
        // Eliminar la ruta base de la URI
        if ($baseDirectory !== '/') {
            $uri = substr($uri, strlen($baseDirectory));
        }
        
        // Eliminar la barra inicial si existe
        if ($uri !== '' && $uri[0] === '/') {
            $uri = substr($uri, 1);
        }
        
        return $uri;
    }
    
    /**
     * Registra las rutas de la aplicación
     */
    protected function registerRoutes()
    {
        // Ruta home
        $this->router->add('', ['controller' => 'HomeController', 'action' => 'index']);
        $this->router->add('index.php', ['controller' => 'HomeController', 'action' => 'index']);
        
        // Rutas de autenticación
        $this->router->add('login', ['controller' => 'AuthController', 'action' => 'showLoginForm']);
        $this->router->add('login/auth', ['controller' => 'AuthController', 'action' => 'login']);
        $this->router->add('logout', ['controller' => 'AuthController', 'action' => 'logout']);
        $this->router->add('registro', ['controller' => 'AuthController', 'action' => 'showRegisterForm']);
        $this->router->add('registro/crear', ['controller' => 'AuthController', 'action' => 'register']);
        
        // Rutas de perfil de usuario
        $this->router->add('perfil', ['controller' => 'UserController', 'action' => 'profile']);
        
        // Rutas de productos
        $this->router->add('productos', ['controller' => 'ProductController', 'action' => 'index']);
    }
    
    /**
     * Ejecuta la aplicación
     */
    public function run()
    {
        try {
            $this->router->dispatch($this->requestUrl);
        } catch (\Exception $e) {
            // Manejar los errores
            echo "<h1>Error</h1>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }
}