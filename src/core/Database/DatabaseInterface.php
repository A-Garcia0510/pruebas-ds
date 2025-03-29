<?php
// src/Core/Database/DatabaseInterface.php
namespace App\Core\Database;

interface DatabaseInterface
{
    /**
     * Obtiene la conexión a la base de datos
     * @return mixed La conexión a la base de datos
     */
    public function getConnection();
    
    /**
     * Prepara una consulta SQL
     * @param string $sql La consulta SQL a preparar
     * @return mixed El statement preparado
     */
    public function prepare($sql);
    
    /**
     * Ejecuta una consulta SQL
     * @param string $sql La consulta SQL a ejecutar
     * @return mixed El resultado de la consulta
     */
    public function query($sql);
    
    /**
     * Cierra la conexión a la base de datos
     */
    public function close();
}