<?php
// src/Metrics/MetricsFactory.php
namespace Metrics;

use Metrics\InfluxDBConfiguration;
use Metrics\Services\InfluxDBClient;

class MetricsFactory {
    // Configuración estática para la conexión
    private static string $url = "http://localhost:8086";
    private static string $user = "Anthony";
    private static string $password = "Anto0410";
    private static string $database = "metricas_carga";
    
    /**
     * Crea una instancia del cliente InfluxDB v1
     * 
     * @return InfluxDBClient
     */
    public static function createInfluxDBClient(): InfluxDBClient {
        // En producción, estas credenciales deberían estar en un archivo de configuración seguro
        $config = new InfluxDBConfiguration(
            self::$url,      // URL de tu servidor InfluxDB
            self::$user,     // Usuario en InfluxDB v1
            self::$password, // Contraseña en InfluxDB v1
            self::$database  // Nombre de la base de datos
        );
        
        return new InfluxDBClient($config);
    }
    
    // Métodos para obtener la configuración (solo para depuración)
    public static function getConfiguredUrl(): string {
        return self::$url;
    }
    
    public static function getConfiguredUser(): string {
        return self::$user;
    }
    
    public static function getConfiguredDB(): string {
        return self::$database;
    }
}