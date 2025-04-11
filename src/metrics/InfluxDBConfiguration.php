<?php
// src/Metrics/InfluxDBConfiguration.php
namespace Metrics;

class InfluxDBConfiguration {
    private string $url;
    private string $token;
    private string $org;
    private string $bucket;
    
    /**
     * Constructor para la configuración de InfluxDB
     * 
     * @param string $url URL del servidor InfluxDB (ej: http://localhost:8086)
     * @param string $token Token de autenticación para InfluxDB
     * @param string $org Nombre de la organización en InfluxDB
     * @param string $bucket Bucket donde se almacenarán los datos
     */
    public function __construct(string $url, string $token, string $org, string $bucket) {
        $this->url = $url;
        $this->token = $token;
        $this->org = $org;
        $this->bucket = $bucket;
    }
    
    // Getters
    public function getUrl(): string {
        return $this->url;
    }
    
    public function getToken(): string {
        return $this->token;
    }
    
    public function getOrg(): string {
        return $this->org;
    }
    
    public function getBucket(): string {
        return $this->bucket;
    }
}