<?php
namespace App\Core;

use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;

/**
 * Clase principal de la aplicación
 */
class App
{
    public static $app;
    public $router;
    public $request;
    public $response;
    public $db;
    public $config;
    
    /**
     * Constructor de la aplicación
     * 
     * @param array $config Configuración de la aplicación
     */
    public function __construct($config = [])
    {
        self::$app = $this;
        $this->config = $config;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router();
        
        // Inicializar la base de datos si hay configuración
        if (isset($config['database'])) {
            $dbConfig = new DatabaseConfiguration(
                $config['database']['host'],
                $config['database']['username'],
                $config['database']['password'],
                $config['database']['database']
            );
            
            $this->db = new MySQLDatabase($dbConfig);
        }
    }
    
    /**
     * Ejecuta la aplicación
     * 
     * @return mixed
     */
    public function run()
    {
        echo $this->router->resolve($this->request, $this->response);
    }
    
    /**
     * Obtiene la instancia de la base de datos
     * 
     * @return \App\Core\Database\MySQLDatabase
     */
    public function getDB()
    {
        return $this->db;
    }
}