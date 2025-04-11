<?php
// src/Metrics/MetricsFactory.php
namespace Metrics;

use Metrics\InfluxDBConfiguration;
use Metrics\Services\InfluxDBClient;

class MetricsFactory {
    /**
     * Crea una instancia del cliente InfluxDB
     * 
     * @return InfluxDBClient
     */
    public static function createInfluxDBClient(): InfluxDBClient {
        // En producción, estas credenciales deberían estar en un archivo de configuración seguro
        $config = new InfluxDBConfiguration(
            "http://localhost:8086", // URL de tu contenedor Docker de InfluxDB
            "tu_token_de_influxdb",  // Token de autenticación
            "tu_organizacion",       // Nombre de tu organización en InfluxDB
            "tu_bucket"              // Nombre del bucket
        );
        
        return new InfluxDBClient($config);
    }
}