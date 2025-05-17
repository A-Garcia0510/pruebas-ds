<?php
// app/core/Database/DatabaseConfiguration.php
namespace App\Core\Database;

class DatabaseConfiguration
{
    private $host;
    private $username;
    private $password;
    private $database;
    
    /**
     * Constructor para la configuración de la base de datos
     * 
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     */
    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }
    
    /**
     * Obtiene el host
     * 
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }
    
    /**
     * Obtiene el nombre de usuario
     * 
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
    
    /**
     * Obtiene la contraseña
     * 
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    
    /**
     * Obtiene el nombre de la base de datos
     * 
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }
}