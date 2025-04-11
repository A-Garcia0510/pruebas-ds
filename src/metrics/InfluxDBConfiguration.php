<?php
// src/Metrics/InfluxDBConfiguration.php
namespace Metrics;

class InfluxDBConfiguration {
    private string $url;
    private string $token; // Será la contraseña en v1
    private string $org;   // Será el usuario en v1
    private string $bucket; // Será la base de datos en v1
    
    /**
     * Constructor para la configuración de InfluxDB v1
     * 
     * @param string $url URL del servidor InfluxDB (ej: http://localhost:8086)
     * @param string $org Nombre de usuario para InfluxDB v1
     * @param string $token Contraseña para InfluxDB v1
     * @param string $bucket Nombre de la base de datos
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