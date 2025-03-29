<?php
// src/Core/Database/DatabaseConfiguration.php
namespace App\Core\Database;

class DatabaseConfiguration
{
    private $host;
    private $username;
    private $password;
    private $database;
    
    /**
     * Constructor de la configuración de base de datos
     * 
     * @param string $host     El host de la base de datos
     * @param string $username El nombre de usuario
     * @param string $password La contraseña
     * @param string $database El nombre de la base de datos
     */
    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }
    
    /**
     * @return string El host de la base de datos
     */
    public function getHost(): string
    {
        return $this->host;
    }
    
    /**
     * @return string El nombre de usuario
     */
    public function getUsername(): string
    {
        return $this->username;
    }
    
    /**
     * @return string La contraseña
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    
    /**
     * @return string El nombre de la base de datos
     */
    public function getDatabase(): string
    {
        return $this->database;
    }
}