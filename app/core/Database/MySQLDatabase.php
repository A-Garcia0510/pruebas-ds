<?php
// app/core/Database/MySQLDatabase.php
namespace App\Core\Database;

class MySQLDatabase implements DatabaseInterface
{
    private $connection;
    private $config;
    
    /**
     * Constructor para la base de datos MySQL
     * 
     * @param DatabaseConfiguration $config
     */
    public function __construct(DatabaseConfiguration $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Establece la conexión a la base de datos
     */
    private function connect()
    {
        $this->connection = new \mysqli(
            $this->config->getHost(),
            $this->config->getUsername(),
            $this->config->getPassword(),
            $this->config->getDatabase()
        );
        
        if ($this->connection->connect_error) {
            throw new DatabaseConnectionException($this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8");
    }
    
    /**
     * Obtiene la conexión a la base de datos
     * 
     * @return \mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Cierra la conexión a la base de datos
     */
    public function closeConnection()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Destructor para cerrar la conexión automáticamente
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}