<?php
// src/Metrics/ServerMonitor.php
namespace Metrics;

use Metrics\Interfaces\InfluxDBClientInterface;

/**
 * Clase para monitorear recursos del servidor durante pruebas de carga
 */
class ServerMonitor {
    private $influxClient;
    private $testId;
    private $startTime;
    private $maxCpuUsage = 0;
    private $maxMemoryUsage = 0;
    private $lastStats = [];
    private $osType;
    
    /**
     * Constructor
     * 
     * @param InfluxDBClientInterface $influxClient Cliente InfluxDB para registrar métricas
     * @param string $testId ID de la prueba asociada
     */
    public function __construct(InfluxDBClientInterface $influxClient, string $testId) {
        $this->influxClient = $influxClient;
        $this->testId = $testId;
        $this->startTime = microtime(true);
        
        // Detectar sistema operativo
        $this->osType = stripos(PHP_OS, 'WIN') === 0 ? 'windows' : 'unix';
        
        // Registro inicial
        $this->recordStats();
    }
    
    /**
     * Obtiene estadísticas actuales del sistema
     * 
     * @return array Array con información de CPU, memoria, etc.
     */
    public function getServerStats(): array {
        $stats = [];
        
        try {
            if ($this->osType === 'windows') {
                $stats = $this->getWindowsStats();
            } else {
                $stats = $this->getUnixStats();
            }
            
            // Actualizar máximos
            if (isset($stats['cpu_usage']) && $stats['cpu_usage'] > $this->maxCpuUsage) {
                $this->maxCpuUsage = $stats['cpu_usage'];
            }
            
            if (isset($stats['memory_usage']) && $stats['memory_usage'] > $this->maxMemoryUsage) {
                $this->maxMemoryUsage = $stats['memory_usage'];
            }
            
            $this->lastStats = $stats;
            return $stats;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas del servidor: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtiene estadísticas para sistemas Windows
     */
    private function getWindowsStats(): array {
        $stats = [];
        
        // CPU - usando WMI
        try {
            // Ejecutar un comando para obtener el uso de CPU
            $cmd = 'wmic cpu get loadpercentage';
            $output = [];
            exec($cmd, $output);
            
            if (isset($output[1])) {
                $stats['cpu_usage'] = (float)trim($output[