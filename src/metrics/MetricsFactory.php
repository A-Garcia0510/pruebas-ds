<?php
// src/Metrics/MetricsFactory.php
namespace Metrics;

use Metrics\InfluxDBConfiguration;
use Metrics\Services\InfluxDBClient;

class MetricsFactory {
    /**
     * Crea una instancia del cliente InfluxDB v1
     * 
     * @return InfluxDBClient
     */
    public static function createInfluxDBClient(): InfluxDBClient {
        // En producción, estas credenciales deberían estar en un archivo de configuración seguro
        $config = new InfluxDBConfiguration(
            "http://localhost:8086", // URL de tu servidor InfluxDB
            "Anthony",             // Usuario en InfluxDB v1
            "Anto0410",             // Contraseña en InfluxDB v1
            "metricas_carga"      // Nombre de la base de datos
        );
        
        return new InfluxDBClient($config);
    }
}