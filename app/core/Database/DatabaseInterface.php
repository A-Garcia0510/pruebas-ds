<?php
// app/core/Database/DatabaseInterface.php
namespace App\Core\Database;

interface DatabaseInterface
{
    /**
     * Obtiene la conexión a la base de datos
     * 
     * @return \mysqli
     */
    public function getConnection();
}