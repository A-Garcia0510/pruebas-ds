<?php
// app/core/Database/MySQLDatabase.php
namespace App\Core\Database;

class MySQLDatabase implements DatabaseInterface
{
    private $connection;
    private $config;
    private $inTransaction = false;
    
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
            throw new DatabaseConnectionException("Error de conexión: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset("utf8mb4");
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
     * Ejecuta una consulta SQL
     * 
     * @param string $sql La consulta SQL a ejecutar
     * @param array $params Los parámetros para la consulta preparada
     * @return \mysqli_stmt|bool
     */
    public function query(string $sql, array $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            throw new \Exception("Error en la preparación de la consulta: " . $this->connection->error);
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }
            
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene una fila de la base de datos
     * 
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Obtiene múltiples filas de la base de datos
     * 
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros para la consulta preparada
     * @return array
     */
    public function fetchAll(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Inserta datos en la base de datos
     * 
     * @param string $table La tabla donde insertar
     * @param array $data Los datos a insertar
     * @return int|bool El ID del último registro insertado o false si falla
     */
    public function insert(string $table, array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->query($sql, array_values($data));
        $insertId = $stmt->insert_id;
        $stmt->close();
        
        return $insertId ?: false;
    }

    /**
     * Actualiza datos en la base de datos
     * 
     * @param string $table La tabla a actualizar
     * @param array $data Los datos a actualizar
     * @param string $where La condición WHERE
     * @param array $whereParams Los parámetros para la condición WHERE
     * @return bool
     */
    public function update(string $table, array $data, string $where, array $whereParams = [])
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        
        return $success;
    }

    /**
     * Elimina datos de la base de datos
     * 
     * @param string $table La tabla de donde eliminar
     * @param string $where La condición WHERE
     * @param array $whereParams Los parámetros para la condición WHERE
     * @return bool
     */
    public function delete(string $table, string $where, array $whereParams = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        
        return $success;
    }

    /**
     * Inicia una transacción
     * 
     * @return bool
     */
    public function beginTransaction()
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->connection->begin_transaction();
        }
        return $this->inTransaction;
    }

    /**
     * Confirma una transacción
     * 
     * @return bool
     */
    public function commit()
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->connection->commit();
        }
        return false;
    }

    /**
     * Revierte una transacción
     * 
     * @return bool
     */
    public function rollback()
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->connection->rollback();
        }
        return false;
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

    /**
     * Indica si hay una transacción activa
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }
}