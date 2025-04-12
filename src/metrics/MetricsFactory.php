<?php
// src/Metrics/MetricsFactory.php
namespace Metrics;

use Metrics\InfluxDBConfiguration;
use Metrics\Services\InfluxDBClient;

class MetricsFactory {
    // Configuración estática para la conexión a InfluxDB v2
    private static string $url = "http://localhost:8086";
    private static string $org = "WorKout"; // Organización en InfluxDB v2
    private static string $token = "I18lFX7jQ07-fYYjKeRRG5gOvcx9pmnmmTbZVWL985nZsdSUoOCAAVQkmRYNpcZQg6y_HH9n0YHBKg-yERo7IQ=="; // Token de acceso
    private static string $bucket = "metricas_carga"; // Bucket en lugar de base de datos
    
    /**
     * Crea una instancia del cliente InfluxDB v2
     * 
     * @return InfluxDBClient
     */
    public static function createInfluxDBClient(): InfluxDBClient {
        // En producción, estas credenciales deberían estar en un archivo de configuración seguro
        $config = new InfluxDBConfiguration(
            self::$url,   // URL de tu servidor InfluxDB
            self::$org,   // Organización en InfluxDB v2
            self::$token, // Token de acceso en InfluxDB v2
            self::$bucket // Nombre del bucket
        );
        
        return new InfluxDBClient($config);
    }
    
    // Métodos para obtener la configuración (solo para depuración)
    public static function getConfiguredUrl(): string {
        return self::$url;
    }
    
    public static function getConfiguredOrg(): string {
        return self::$org;
    }
    
    public static function getConfiguredBucket(): string {
        return self::$bucket;
    }
}