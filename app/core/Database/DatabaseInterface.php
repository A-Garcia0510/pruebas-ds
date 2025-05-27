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

    /**
     * Ejecuta una consulta SQL
     * 
     * @param string $sql La consulta SQL a ejecutar
     * @param array $params Los parámetros para la consulta preparada
     * @return \mysqli_stmt|bool
     */
    public function query(string $sql, array $params = []);

    /**
     * Obtiene una fila de la base de datos
     * 
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []);

    /**
     * Obtiene múltiples filas de la base de datos
     * 
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return array
     */
    public function fetchAll(string $sql, array $params = []);

    /**
     * Inserta datos en la base de datos
     * 
     * @param string $table La tabla donde insertar
     * @param array $data Los datos a insertar
     * @return int|bool El ID del último registro insertado o false si falla
     */
    public function insert(string $table, array $data);

    /**
     * Actualiza datos en la base de datos
     * 
     * @param string $table La tabla a actualizar
     * @param array $data Los datos a actualizar
     * @param string $where La condición WHERE
     * @param array $whereParams Los parámetros para la condición WHERE
     * @return bool
     */
    public function update(string $table, array $data, string $where, array $whereParams = []);

    /**
     * Elimina datos de la base de datos
     * 
     * @param string $table La tabla de donde eliminar
     * @param string $where La condición WHERE
     * @param array $whereParams Los parámetros para la condición WHERE
     * @return bool
     */
    public function delete(string $table, string $where, array $whereParams = []);

    /**
     * Inicia una transacción
     * 
     * @return bool
     */
    public function beginTransaction();

    /**
     * Confirma una transacción
     * 
     * @return bool
     */
    public function commit();

    /**
     * Revierte una transacción
     * 
     * @return bool
     */
    public function rollback();
}