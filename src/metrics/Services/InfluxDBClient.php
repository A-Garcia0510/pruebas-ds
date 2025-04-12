<?php
// src/Metrics/Services/InfluxDBClient.php
namespace Metrics\Services;

use Metrics\InfluxDBConfiguration;
use Metrics\Interfaces\InfluxDBClientInterface;

class InfluxDBClient implements InfluxDBClientInterface {
    private $client;
    private $database;
    private InfluxDBConfiguration $config;
    private bool $connected = false;
    private $lastError = ''; // Propiedad para almacenar el último error

    public function __construct(InfluxDBConfiguration $config) {
        $this->config = $config;
        try {
            // Para InfluxDB v1
            $this->client = new \InfluxDB\Client(
                parse_url($config->getUrl(), PHP_URL_HOST),
                parse_url($config->getUrl(), PHP_URL_PORT) ?: 8086,
                $config->getOrg(),  // En v1 esto funcionaría como username
                $config->getToken() // En v1 esto funcionaría como password
            );
            
            // Simplemente seleccionamos la base de datos, sin verificar si existe todavía
            $this->database = $this->client->selectDB($config->getBucket());
            
            // Hacemos una consulta simple para verificar la conexión
            $this->connected = $this->pingDatabase();
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error conectando a InfluxDB: " . $e->getMessage());
            $this->connected = false;
        }
    }

    /**
     * Método privado para probar la conexión con una consulta sencilla
     */
    private function pingDatabase(): bool {
        try {
            // Intentamos usar un método más directo y seguro
            $databases = $this->client->listDatabases();
            return true;
        } catch (\Exception $e) {
            try {
                // Intentamos como alternativa un ping simple
                $this->client->ping();
                return true;
            } catch (\Exception $innerEx) {
                $this->lastError = $innerEx->getMessage();
                error_log("Error haciendo ping a InfluxDB: " . $innerEx->getMessage());
                return false;
            }
        }
    }

    /**
     * Escribe datos en InfluxDB
     * 
     * @param string $measurement Nombre de la medición
     * @param array $fields Campos a guardar (valores)
     * @param array $tags Tags asociados (metadata)
     * @return bool
     */
    public function writeData(string $measurement, array $fields, array $tags = []): bool {
        if (!$this->connected) {
            $this->lastError = "No hay conexión con InfluxDB";
            return false;
        }
        
        try {
            $point = new \InfluxDB\Point(
                $measurement,
                null,
                $tags,
                $fields,
                time()
            );
    
            $result = $this->database->writePoints([$point], \InfluxDB\Database::PRECISION_SECONDS);
            if (!$result) {
                $this->lastError = "No se pudo escribir en InfluxDB - Resultado falso";
                error_log($this->lastError);
            }
            return $result;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error escribiendo en InfluxDB: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Ejecuta una consulta en InfluxDB de manera segura
     * 
     * @param string $query Consulta en lenguaje InfluxQL
     * @return array Resultados de la consulta
     */
    public function query(string $query): array {
        if (!$this->connected) {
            $this->lastError = "No hay conexión con InfluxDB";
            return [];
        }
        
        try {
            // Manejo especial para comandos específicos
            if (strtoupper(substr(trim($query), 0, 14)) === 'SHOW DATABASES') {
                try {
                    $databases = $this->client->listDatabases();
                    $result = [];
                    foreach ($databases as $dbName) {
                        $result[] = ['name' => $dbName];
                    }
                    return $result;
                } catch (\Exception $e) {
                    $this->lastError = "Error al listar bases de datos: " . $e->getMessage();
                    return [];
                }
            } else if (strtoupper(substr(trim($query), 0, 15)) === 'CREATE DATABASE') {
                // Extraer el nombre de la base de datos
                $dbName = trim(substr(trim($query), 15));
                try {
                    $this->client->createDatabase($dbName);
                    return [['success' => true]];
                } catch (\Exception $e) {
                    $this->lastError = "Error al crear la base de datos: " . $e->getMessage();
                    return [];
                }
            }
            
            // Para otras consultas, usar el método normal
            $result = $this->client->query($query, $this->config->getBucket());
            
            // Verificar si el resultado es válido
            if ($result) {
                try {
                    return $result->getPoints();
                } catch (\Exception $e) {
                    $this->lastError = "Error al obtener puntos: " . $e->getMessage();
                    return [];
                }
            } else {
                $this->lastError = "La consulta no devolvió resultados";
                return [];
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error consultando InfluxDB: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si la conexión está activa
     * 
     * @return bool
     */
    public function isConnected(): bool {
        return $this->connected;
    }
    
    /**
     * Obtiene el último mensaje de error
     * 
     * @return string
     */
    public function getLastError(): string {
        return $this->lastError;
    }
}