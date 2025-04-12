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
            error_log("Error conectando a InfluxDB: " . $e->getMessage());
            $this->connected = false;
        }
    }

    /**
     * Método privado para probar la conexión con una consulta sencilla
     */
    private function pingDatabase(): bool {
        try {
            // Consulta muy sencilla que debería funcionar si hay conexión
            $query = 'SHOW DATABASES';
            $this->client->query($query);
            return true;
        } catch (\Exception $e) {
            error_log("Error haciendo ping a InfluxDB: " . $e->getMessage());
            return false;
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
        if (!$this->connected) return false;
        
        try {
            $point = new \InfluxDB\Point(
                $measurement, // Nombre de la medición
                null,         // Valor (puede ser null si solo usas campos)
                $tags,        // Tags
                $fields,      // Campos
                time()        // Timestamp
            );

            return $this->database->writePoints([$point], \InfluxDB\Database::PRECISION_SECONDS);
        } catch (\Exception $e) {
            error_log("Error escribiendo en InfluxDB: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ejecuta una consulta en InfluxDB
     * 
     * @param string $query Consulta en lenguaje InfluxQL
     * @return array Resultados de la consulta
     */
    public function query(string $query): array {
        if (!$this->connected) return [];
        
        try {
            $result = $this->database->query($query);
            return $result->getPoints();
        } catch (\Exception $e) {
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
}