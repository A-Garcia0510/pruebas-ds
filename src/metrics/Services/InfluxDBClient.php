<?php
// src/Metrics/Services/InfluxDBClient.php
namespace Metrics\Services;

use InfluxDB\Client;
use InfluxDB\Database;
use Metrics\InfluxDBConfiguration;
use Metrics\Interfaces\InfluxDBClientInterface;

class InfluxDBClient implements InfluxDBClientInterface {
    private Client $client;
    private Database $database;
    private InfluxDBConfiguration $config;

    public function __construct(InfluxDBConfiguration $config) {
        $this->config = $config;
        $this->client = new Client(
            $config->getHost(),
            $config->getPort(),
            $config->getUsername(),
            $config->getPassword()
        );
        $this->database = $this->client->selectDB($config->getDatabase());
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
        try {
            $point = new \InfluxDB\Point(
                $measurement, // Nombre de la medición
                null,         // Valor (puede ser null si solo usas campos)
                $tags,        // Tags
                $fields,      // Campos
                time()        // Timestamp
            );

            $this->database->writePoints([$point], \InfluxDB\Database::PRECISION_SECONDS);
            return true;
        } catch (\Exception $e) {
            // Manejo de errores
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
        try {
            return $this->database->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}