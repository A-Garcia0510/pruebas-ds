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
                $stats['cpu_usage'] = (float)trim($output[1]);
            } else {
                $stats['cpu_usage'] = 0;
            }
            
            // Memoria - usando systeminfo
            $cmd = 'systeminfo | findstr "Memory"';
            $output = [];
            exec($cmd, $output);
            
            $totalMemory = 0;
            $availableMemory = 0;
            
            foreach ($output as $line) {
                if (preg_match('/Total Physical Memory:\s+([0-9,]+)\s+MB/i', $line, $matches)) {
                    $totalMemory = (int)str_replace(',', '', $matches[1]);
                }
                if (preg_match('/Available Physical Memory:\s+([0-9,]+)\s+MB/i', $line, $matches)) {
                    $availableMemory = (int)str_replace(',', '', $matches[1]);
                }
            }
            
            if ($totalMemory > 0) {
                $usedMemory = $totalMemory - $availableMemory;
                $stats['memory_usage'] = round(($usedMemory / $totalMemory) * 100, 2);
                $stats['total_memory_mb'] = $totalMemory;
                $stats['available_memory_mb'] = $availableMemory;
            } else {
                $stats['memory_usage'] = 0;
                $stats['total_memory_mb'] = 0;
                $stats['available_memory_mb'] = 0;
            }
            
            // Disco
            $cmd = 'wmic logicaldisk get size,freespace,caption';
            $output = [];
            exec($cmd, $output);
            
            $diskStats = [];
            foreach ($output as $i => $line) {
                if ($i > 0 && !empty(trim($line))) {
                    $parts = preg_split('/\s+/', trim($line), -1, PREG_SPLIT_NO_EMPTY);
                    if (count($parts) >= 3) {
                        $drive = $parts[0];
                        $freeSpace = (float)$parts[1];
                        $totalSpace = (float)$parts[2];
                        
                        if ($totalSpace > 0) {
                            $usedPercentage = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
                            $diskStats[$drive] = [
                                'total_gb' => round($totalSpace / (1024 * 1024 * 1024), 2),
                                'free_gb' => round($freeSpace / (1024 * 1024 * 1024), 2),
                                'usage_percent' => $usedPercentage
                            ];
                        }
                    }
                }
            }
            
            $stats['disk_stats'] = $diskStats;
            
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas de Windows: " . $e->getMessage());
            $stats['error_windows'] = $e->getMessage();
        }
        
        return $stats;
    }
    
    /**
     * Obtiene estadísticas para sistemas Unix/Linux
     */
    private function getUnixStats(): array {
        $stats = [];
        
        // CPU
        try {
            // Método 1: usando top
            $cmd = "top -bn1 | grep 'Cpu(s)' | awk '{print $2 + $4}'";
            $cpuUsage = trim(shell_exec($cmd));
            
            if (empty($cpuUsage) || !is_numeric($cpuUsage)) {
                // Método 2: usando /proc/stat
                $load = sys_getloadavg();
                $cpuCores = trim(shell_exec("nproc"));
                
                if (!empty($cpuCores) && is_numeric($cpuCores) && $cpuCores > 0) {
                    $cpuUsage = round(($load[0] / $cpuCores) * 100, 2);
                } else {
                    $cpuUsage = round($load[0] * 25, 2); // Estimación
                }
            }
            
            $stats['cpu_usage'] = (float)$cpuUsage;
            $stats['cpu_cores'] = (int)trim(shell_exec("nproc"));
            $stats['load_avg'] = sys_getloadavg();
            
            // Memoria
            $cmd = "free | grep Mem | awk '{print $2,$3,$4,$5,$6,$7}'";
            $memInfo = explode(' ', trim(shell_exec($cmd)));
            
            if (count($memInfo) >= 2) {
                $totalMemory = (int)$memInfo[0];
                $usedMemory = (int)$memInfo[1];
                
                if ($totalMemory > 0) {
                    $stats['memory_usage'] = round(($usedMemory / $totalMemory) * 100, 2);
                    $stats['total_memory_kb'] = $totalMemory;
                    $stats['used_memory_kb'] = $usedMemory;
                    $stats['free_memory_kb'] = $totalMemory - $usedMemory;
                }
            }
            
            // Disco
            $cmd = "df -h | grep -v 'tmpfs\|cdrom'";
            $output = [];
            exec($cmd, $output);
            
            $diskStats = [];
            foreach ($output as $i => $line) {
                if ($i > 0) { // Saltar la primera línea (encabezado)
                    $parts = preg_split('/\s+/', trim($line));
                    if (count($parts) >= 5) {
                        $filesystem = $parts[0];
                        $size = $parts[1];
                        $used = $parts[2];
                        $avail = $parts[3];
                        $usedPercent = (int)str_replace('%', '', $parts[4]);
                        
                        $diskStats[$filesystem] = [
                            'size' => $size,
                            'used' => $used,
                            'available' => $avail,
                            'usage_percent' => $usedPercent
                        ];
                    }
                }
            }
            
            $stats['disk_stats'] = $diskStats;
            
            // Procesos y usuarios
            $stats['process_count'] = (int)trim(shell_exec("ps aux | wc -l"));
            $stats['user_count'] = (int)trim(shell_exec("who | wc -l"));
            
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas de Unix: " . $e->getMessage());
            $stats['error_unix'] = $e->getMessage();
        }
        
        return $stats;
    }
    
    /**
     * Registra las estadísticas actuales en InfluxDB
     * 
     * @return bool Éxito de la operación
     */
    public function recordStats(): bool {
        try {
            $stats = $this->getServerStats();
            
            if (isset($stats['error'])) {
                return false;
            }
            
            // Preparar los campos para InfluxDB
            $fields = [
                'cpu_usage' => $stats['cpu_usage'] ?? 0,
                'memory_usage' => $stats['memory_usage'] ?? 0,
                'elapsed_time' => microtime(true) - $this->startTime
            ];
            
            // Añadir campos adicionales dependiendo del OS
            if ($this->osType === 'unix') {
                $fields['load_avg_1m'] = $stats['load_avg'][0] ?? 0;
                $fields['load_avg_5m'] = $stats['load_avg'][1] ?? 0;
                $fields['load_avg_15m'] = $stats['load_avg'][2] ?? 0;
                $fields['process_count'] = $stats['process_count'] ?? 0;
                $fields['user_count'] = $stats['user_count'] ?? 0;
            }
            
            // Registrar en InfluxDB
            $result = $this->influxClient->writeData(
                'server_resources',
                $fields,
                [
                    'test_id' => $this->testId,
                    'os_type' => $this->osType
                ]
            );
            
            // Registrar estadísticas de disco por separado (son muchas)
            if (isset($stats['disk_stats']) && !empty($stats['disk_stats'])) {
                foreach ($stats['disk_stats'] as $device => $diskStat) {
                    $diskFields = [
                        'usage_percent' => $diskStat['usage_percent'] ?? 0
                    ];
                    
                    // Añadir campos específicos según el tipo de OS
                    if ($this->osType === 'windows') {
                        $diskFields['total_gb'] = $diskStat['total_gb'] ?? 0;
                        $diskFields['free_gb'] = $diskStat['free_gb'] ?? 0;
                    } else {
                        $diskFields['size'] = str_replace(['G', 'T', 'M'], '', $diskStat['size'] ?? '0');
                        $diskFields['available'] = str_replace(['G', 'T', 'M'], '', $diskStat['available'] ?? '0');
                    }
                    
                    $this->influxClient->writeData(
                        'disk_resources',
                        $diskFields,
                        [
                            'test_id' => $this->testId,
                            'device' => str_replace('/', '_', $device)
                        ]
                    );
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Error registrando estadísticas del servidor: " . $e->getMessage());
            return false;
        }
    }
    
    // Getters para valores máximos
    
    /**
     * Obtiene el uso máximo de CPU registrado
     * 
     * @return float
     */
    public function getMaxCpuUsage(): float {
        return $this->maxCpuUsage;
    }
    
    /**
     * Obtiene el uso máximo de memoria registrado
     * 
     * @return float
     */
    public function getMaxMemoryUsage(): float {
        return $this->maxMemoryUsage;
    }
    
    /**
     * Obtiene las últimas estadísticas registradas
     * 
     * @return array
     */
    public function getLastStats(): array {
        return $this->lastStats;
    }
    
    /**
     * Obtiene el tiempo transcurrido desde el inicio del monitoreo
     * 
     * @return float Tiempo en segundos
     */
    public function getElapsedTime(): float {
        return microtime(true) - $this->startTime;
    }
}