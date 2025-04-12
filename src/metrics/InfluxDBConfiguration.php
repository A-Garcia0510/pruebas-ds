<?php
// src/Metrics/InfluxDBConfiguration.php
namespace Metrics;

class InfluxDBConfiguration {
    private string $url;
    private string $token;
    private string $org;
    private string $bucket;
    
    /**
     * Constructor para la configuración de InfluxDB v2
     * 
     * @param string $url URL del servidor InfluxDB (ej: http://localhost:8086)
     * @param string $org Nombre de la organización en InfluxDB v2
     * @param string $token Token de API para InfluxDB v2
     * @param string $bucket Nombre del bucket
     */
    public function __construct(string $url, string $org, string $token, string $bucket) {
        $this->url = $url;
        $this->org = $org;
        $this->token = $token;
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