<?php
// PHP/load_monitor.php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

/**
 * Clase para monitorear la carga de la página index.php
 */
class LoadMonitor {
    private $influxClient;
    private $startTime;
    private $endTime;
    private $memoryStart;
    private $memoryPeak;
    private $sessionData;
    private $clientIP;
    private $userAgent;
    private $requestMethod;
    private $requestUri;
    private $responseCode;
    private $loadID;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Generar un ID único para esta carga
        $this->loadID = uniqid('load_');
        
        // Capturar el tiempo de inicio y memoria
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage();
        
        // Capturar información del cliente
        $this->clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        $this->requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        // Información de sesión
        $this->sessionData = [
            'active' => isset($_SESSION) && !empty($_SESSION),
            'user_logged' => isset($_SESSION['correo']),
            'session_id' => session_id() ?: 'none'
        ];
        
        // Crear cliente InfluxDB
        try {
            $this->influxClient = MetricsFactory::createInfluxDBClient();
        } catch (Exception $e) {
            error_log("Error al inicializar monitor de carga: " . $e->getMessage());
        }
    }
    
    /**
     * Finaliza el monitoreo y registra las métricas
     * 
     * @param int $responseCode Código de respuesta HTTP
     * @return bool Éxito de la operación
     */
    public function finalize($responseCode = 200): bool {
        $this->endTime = microtime(true);
        $this->memoryPeak = memory_get_peak_usage();
        $this->responseCode = $responseCode;
        
        // Calcular métricas
        $loadTime = ($this->endTime - $this->startTime) * 1000; // en milisegundos
        $memoryUsed = $this->memoryPeak - $this->memoryStart;
        
        // Si no hay cliente influx, no podemos continuar
        if (!$this->influxClient || !$this->influxClient->isConnected()) {
            error_log("No se pudo registrar métricas: Cliente InfluxDB no disponible");
            return false;
        }
        
        // Registrar en InfluxDB
        try {
            // Campos con valores numéricos para métricas
            $fields = [
                'load_time_ms' => $loadTime,
                'memory_start_bytes' => $this->memoryStart,
                'memory_peak_bytes' => $this->memoryPeak,
                'memory_used_bytes' => $memoryUsed,
                'response_code' => $this->responseCode,
                'is_logged' => $this->sessionData['user_logged'] ? 1 : 0
            ];
            
            // Tags para agrupar y filtrar (metadata)
            $tags = [
                'load_id' => $this->loadID,
                'client_ip' => $this->clientIP,
                'user_agent_hash' => md5($this->userAgent), // Convertir a hash para privacidad
                'request_method' => $this->requestMethod,
                'uri' => $this->requestUri,
                'session_active' => $this->sessionData['active'] ? 'true' : 'false',
                'user_logged' => $this->sessionData['user_logged'] ? 'true' : 'false',
                'page' => 'index' // Para identificar esta página en específico
            ];
            
            // Escribir a InfluxDB
            $result = $this->influxClient->writeData('page_load', $fields, $tags);
            
            if (!$result) {
                error_log("Error al registrar métricas: " . $this->influxClient->getLastError());
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error al registrar métricas de carga: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Devuelve métricas actuales como array (para debug)
     */
    public function getMetrics(): array {
        $loadTime = ($this->endTime ?? microtime(true)) - $this->startTime;
        
        return [
            'load_id' => $this->loadID,
            'start_time' => date('Y-m-d H:i:s', (int)$this->startTime),
            'load_time' => number_format($loadTime * 1000, 2) . ' ms',
            'memory_used' => ($this->memoryPeak - $this->memoryStart) . ' bytes',
            'client_ip' => $this->clientIP,
            'user_agent' => $this->userAgent,
            'request_method' => $this->requestMethod,
            'request_uri' => $this->requestUri,
            'session_active' => $this->sessionData['active'],
            'user_logged' => $this->sessionData['user_logged'],
            'session_id' => $this->sessionData['session_id'],
            'response_code' => $this->responseCode ?? 'no finalizado'
        ];
    }
}