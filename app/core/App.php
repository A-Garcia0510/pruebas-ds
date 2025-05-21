<?php
namespace App\Core;

use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Shop\Services\CartService;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Repositories\PurchaseRepository;
use App\Shop\Services\PurchaseService;
use App\Shop\Services\ProductService;
use App\Shop\Commands\CartCommandInvoker;
use App\Controllers\CartController;
use App\Controllers\PageController;
use App\Controllers\ProductController;
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseInterface;
use App\Core\Database\DatabaseConfiguration;
use App\Controllers\DashboardController;
use App\Controllers\AuthController;

/**
 * Clase principal de la aplicación
 */
class App
{
    public static App $app;
    public Router $router;
    public Request $request;
    public Response $response;
    public Container $container;
    public array $config;
    
    /**
     * Constructor de la aplicación
     * 
     * @param array $config Configuración de la aplicación
     */
    public function __construct(array $config)
    {
        self::$app = $this;
        $this->config = $config;
        
        // Inicializar el container
        $this->initializeContainer();
        
        // Registrar interfaces y sus implementaciones
        $this->container->bind(RequestInterface::class, Request::class);
        $this->container->bind(ResponseInterface::class, Response::class);
        $this->container->bind(DatabaseInterface::class, MySQLDatabase::class);
        
        // Registrar servicios como singletons
        $this->container->singleton(Request::class, function($container) {
            return new Request();
        });
        
        $this->container->singleton(Response::class, function($container) {
            return new Response();
        });
        
        // Inicializar la base de datos primero
        $this->initializeDatabase();
        
        // Registrar servicios de la aplicación
        $this->container->singleton(CartService::class, function($container) {
            return new CartService(
                $container->resolve(DatabaseInterface::class),
                $container->resolve(ProductRepository::class)
            );
        });
        
        // Registrar CartService y sus dependencias
        $this->container->singleton(ProductRepository::class, function($container) {
            return new ProductRepository($container->resolve(DatabaseInterface::class));
        });
        
        $this->container->singleton(PurchaseRepository::class, function($container) {
            return new PurchaseRepository($container->resolve(DatabaseInterface::class));
        });
        
        $this->container->singleton(ProductService::class, function($container) {
            return new ProductService($container->resolve(ProductRepository::class));
        });
        
        $this->container->singleton(PurchaseService::class, function($container) {
            return new PurchaseService(
                $container->resolve(DatabaseInterface::class),
                $container->resolve(CartService::class),
                $container->resolve(ProductRepository::class),
                $container->resolve(PurchaseRepository::class)
            );
        });
        
        $this->container->singleton(CartCommandInvoker::class, function($container) {
            return new CartCommandInvoker($container->resolve(CartService::class));
        });
        
        // Registrar CartController
        $this->container->bind(CartController::class, function($container) {
            return new CartController(
                $container->resolve(CartService::class),
                $container->resolve(RequestInterface::class),
                $container->resolve(ResponseInterface::class),
                $container->resolve(ProductRepository::class),
                $container->resolve(PurchaseRepository::class),
                $container->resolve(PurchaseService::class),
                $container->resolve(ProductService::class),
                $container->resolve(CartCommandInvoker::class),
                $container
            );
        });
        
        // Registrar PageController
        $this->container->bind(PageController::class, function($container) {
            return new PageController(
                $container->resolve(RequestInterface::class),
                $container->resolve(ResponseInterface::class),
                $container->resolve(ProductRepository::class),
                $container
            );
        });
        
        // Registrar ProductController
        $this->container->bind(ProductController::class, function($container) {
            return new ProductController(
                $container->resolve(RequestInterface::class),
                $container->resolve(ResponseInterface::class),
                $container->resolve(ProductRepository::class),
                $container
            );
        });
        
        // Registrar Router
        $this->container->bind(Router::class, function($container) {
            return new Router(
                $container->resolve(RequestInterface::class),
                $container->resolve(ResponseInterface::class),
                $container
            );
        });
        
        // Registrar DashboardController
        $this->container->bind(DashboardController::class, function($container) {
            return new DashboardController(
                $container->resolve(Request::class),
                $container->resolve(Response::class),
                $container,
                $container->resolve(DatabaseInterface::class)
            );
        });

        $this->container->bind(AuthController::class, function($container) {
            return new AuthController(
                $container->resolve(Request::class),
                $container->resolve(Response::class),
                $container,
                $container->resolve(DatabaseInterface::class)
            );
        });
        
        // Resolver dependencias
        $this->request = $this->container->resolve(RequestInterface::class);
        $this->response = $this->container->resolve(ResponseInterface::class);
        $this->router = $this->container->resolve(Router::class);
    }
    
    /**
     * Ejecuta la aplicación
     * 
     * @return mixed
     */
    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            if ($this->config['app']['debug'] ?? false) {
                throw $e;
            }
            
            $this->response->setStatusCode(500);
            echo $this->response->error(500, 'Error interno del servidor');
        }
    }
    
    /**
     * Inicializa la base de datos
     */
    private function initializeDatabase()
    {
        if (isset($this->config['database'])) {
            // Registrar la configuración de la base de datos
            $this->container->singleton(DatabaseConfiguration::class, function() {
                return new DatabaseConfiguration(
                    $this->config['database']['host'],
                    $this->config['database']['username'],
                    $this->config['database']['password'],
                    $this->config['database']['database']
                );
            });
            
            // Registrar la instancia de la base de datos
            $this->container->singleton(DatabaseInterface::class, function() {
                $dbConfig = $this->container->resolve(DatabaseConfiguration::class);
                return new MySQLDatabase($dbConfig);
            });
        }
    }
    
    /**
     * Obtiene el contenedor de dependencias
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    private function initializeContainer()
    {
        $this->container = new Container();
        
        // Registrar la configuración como un binding
        $this->container->bind('config', function() {
            return $this->config;
        }, true);
        
        // Registrar servicios core como singletons
        $this->container->singleton(Request::class, function($container) {
            return new Request();
        });
        
        $this->container->singleton(Response::class, function($container) {
            return new Response();
        });
        
        // Registrar interfaces y sus implementaciones
        $this->container->bind(RequestInterface::class, Request::class);
        $this->container->bind(ResponseInterface::class, Response::class);
        $this->container->bind(DatabaseInterface::class, MySQLDatabase::class);

        // Registrar controladores
        $this->container->bind(DashboardController::class, function($container) {
            return new DashboardController(
                $container->resolve(Request::class),
                $container->resolve(Response::class),
                $container,
                $container->resolve(DatabaseInterface::class)
            );
        });

        $this->container->bind(AuthController::class, function($container) {
            return new AuthController(
                $container->resolve(Request::class),
                $container->resolve(Response::class),
                $container,
                $container->resolve(DatabaseInterface::class)
            );
        });
    }
}