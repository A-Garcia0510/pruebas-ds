<?php
// src/Core/Database/MySQLDatabase.php
namespace App\Core\Database;

use mysqli;

class MySQLDatabase implements DatabaseInterface
{
    private $connection;
    private $config;
    
    /**
     * Constructor de la base de datos MySQL
     * 
     * @param DatabaseConfiguration $config La configuración de la base de datos
     * @throws DatabaseConnectionException Si no se puede conectar a la base de datos
     */
    public function __construct(DatabaseConfiguration $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Establece la conexión a la base de datos
     * 
     * @throws DatabaseConnectionException Si no se puede conectar a la base de datos
     */
    private function connect()
    {
        $this->connection = new mysqli(
            $this->config->getHost(),
            $this->config->getUsername(),
            $this->config->getPassword(),
            $this->config->getDatabase()
        );
        
        if ($this->connection->connect_error) {
            throw new DatabaseConnectionException($this->connection->connect_error);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * @inheritDoc
     */
    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }
    
    /**
     * @inheritDoc
     */
    public function query($sql)
    {
        return $this->connection->query($sql);
    }
    
    /**
     * @inheritDoc
     */
    public function close()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}