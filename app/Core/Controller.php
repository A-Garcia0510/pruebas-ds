<?php
// app/Core/Controller.php
namespace App\Core;

use App\Core\Database\DatabaseInterface;
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseConfiguration;
use App\Auth\AuthFactory;
use App\Auth\Interfaces\AuthenticatorInterface;

abstract class Controller
{
    /**
     * @var array
     */
    protected $routeParams = [];
    
    /**
     * @var DatabaseInterface
     */
    protected $database;
    
    /**
     * @var AuthenticatorInterface
     */
    protected $authenticator;
    
    /**
     * Constructor del controlador base
     * 
     * @param array $routeParams Parámetros de la ruta
     */
    public function __construct($routeParams)
    {
        $this->routeParams = $routeParams;
        $this->setupDatabase();
        $this->setupAuthenticator();
    }
    
    /**
     * Configura la conexión a la base de datos
     */
    protected function setupDatabase()
    {
        $config = require_once dirname(dirname(__DIR__)) . '/config/config.php';
        $dbConfig = new DatabaseConfiguration(
            $config['database']['host'],
            $config['database']['username'],
            $config['database']['password'],
            $config['database']['database']
        );
        
        $this->database = new MySQLDatabase($dbConfig);
    }
    
    /**
     * Configura el autenticador
     */
    protected function setupAuthenticator()
    {
        $this->authenticator = AuthFactory::createAuthenticator($this->database);
    }
    
    /**
     * Renderiza una vista
     * 
     * @param string $view La vista a renderizar
     * @param array $args Los argumentos para la vista
     * @return void
     */
    protected function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);
        
        $file = dirname(__DIR__) . "/Views/$view";
        
        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file no encontrado");
        }
    }
    
    /**
     * Redirige a otra URL
     * 
     * @param string $url La URL a redirigir
     * @return void
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Verifica si el usuario está autenticado
     * 
     * @return bool
     */
    protected function isAuthenticated()
    {
        return $this->authenticator->isAuthenticated();
    }
    
    /**
     * Requiere que el usuario esté autenticado
     * 
     * @return void
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
}