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
    private $logger;
    private $lastLogTime = 0;
    private $logFrequency = 5; // segundos entre entradas de log
    
    /**
     * Constructor
     * 
     * @param InfluxDBClientInterface $influxClient Cliente InfluxDB para registrar métricas
     * @param string $testId ID de la prueba asociada
     * @param callable|null $logger Función opcional para logging
     */
    public function __construct(InfluxDBClientInterface $influxClient, string $testId, callable $logger = null) {
        $this->influxClient = $influxClient;
        $this->testId = $testId;
        $this->startTime = microtime(true);
        $this->logger = $logger ?: function($message) {
            error_log("[ServerMonitor] $message");
        };
        
        // Detectar sistema operativo con más detalle
        if (stripos(PHP_OS, 'WIN') === 0) {
            $this->osType = 'windows';
            $this->logMessage("Sistema operativo detectado: Windows " . PHP_OS);
        } elseif (stripos(PHP_OS, 'LINUX') !== false) {
            $this->osType = 'linux';
            $this->logMessage("Sistema operativo detectado: Linux " . PHP_OS);
        } elseif (stripos(PHP_OS, 'DAR') !== false) {
            $this->osType = 'darwin';
            $this->logMessage("Sistema operativo detectado: macOS " . PHP_OS);
        } else {
            $this->osType = 'unix';
            $this->logMessage("Sistema operativo detectado: Unix genérico " . PHP_OS);
        }
        
        // Verificar disponibilidad de comandos necesarios
        $this->checkCommandAvailability();
        
        // Registro inicial
        $this->logMessage("Iniciando monitoreo para test ID: {$testId}");
        $this->recordStats();
    }
    
    /**
     * Verifica la disponibilidad de comandos necesarios según el sistema operativo
     */
    private function checkCommandAvailability() {
        $commandsToCheck = [];
        
        if ($this->osType === 'windows') {
            $commandsToCheck = ['wmic', 'systeminfo'];
        } else {
            $commandsToCheck = ['top', 'free', 'df', 'ps', 'who'];
        }
        
        foreach ($commandsToCheck as $command) {
            $checkCmd = $this->osType === 'windows' ? "where $command" : "which $command";
            $output = [];
            $returnVar = null;
            exec($checkCmd, $output, $returnVar);
            
            if ($returnVar !== 0) {
                $this->logMessage("ADVERTENCIA: Comando '$command' no disponible. Algunas métricas podrían no estar disponibles.");
            }
        }
    }
    
    /**
     * Registra un mensaje en el log con control de frecuencia
     * 
     * @param string $message Mensaje a registrar
     * @param bool $force Forzar el registro sin importar la frecuencia
     */
    private function logMessage(string $message, bool $force = false) {
        $now = microtime(true);
        
        // Solo registrar si han pasado X segundos desde el último log o si se fuerza
        if ($force || ($now - $this->lastLogTime) >= $this->logFrequency) {
            ($this->logger)($message);
            $this->lastLogTime = $now;
        }
    }
    
    /**
     * Obtiene estadísticas actuales del sistema con mejor manejo de errores
     * 
     * @return array Array con información de CPU, memoria, etc.
     */
    public function getServerStats(): array {
        $stats = [
            'timestamp' => microtime(true),
            'elapsed_time' => $this->getElapsedTime()
        ];
        
        try {
            if ($this->osType === 'windows') {
                $winStats = $this->getWindowsStats();
                $stats = array_merge($stats, $winStats);
            } elseif ($this->osType === 'darwin') {
                $macStats = $this->getMacOSStats();
                $stats = array_merge($stats, $macStats);
            } else {
                $nixStats = $this->getUnixStats();
                $stats = array_merge($stats, $nixStats);
            }
            
            // Asegurar valores válidos para CPU y memoria
            if (!isset($stats['cpu_usage']) || !is_numeric($stats['cpu_usage'])) {
                $stats['cpu_usage'] = 0;
                $this->logMessage("ADVERTENCIA: Valor de CPU no válido, establecido a 0");
            }
            
            if (!isset($stats['memory_usage']) || !is_numeric($stats['memory_usage'])) {
                $stats['memory_usage'] = 0;
                $this->logMessage("ADVERTENCIA: Valor de memoria no válido, establecido a 0");
            }
            
            // Validar rango de valores
            $stats['cpu_usage'] = min(100, max(0, $stats['cpu_usage']));
            $stats['memory_usage'] = min(100, max(0, $stats['memory_usage']));
            
            // Actualizar máximos
            if ($stats['cpu_usage'] > $this->maxCpuUsage) {
                $this->maxCpuUsage = $stats['cpu_usage'];
            }
            
            if ($stats['memory_usage'] > $this->maxMemoryUsage) {
                $this->maxMemoryUsage = $stats['memory_usage'];
            }
            
            $this->lastStats = $stats;
            return $stats;
            
        } catch (\Exception $e) {
            $this->logMessage("ERROR obteniendo estadísticas: " . $e->getMessage(), true);
            // Devolver al menos la información básica con valores por defecto
            return array_merge($stats, [
                'cpu_usage' => $this->lastStats['cpu_usage'] ?? 0,
                'memory_usage' => $this->lastStats['memory_usage'] ?? 0,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene estadísticas para sistemas Windows con métodos alternativos
     */
    /**
 * Obtiene estadísticas para sistemas Windows con métodos alternativos
 */
private function getWindowsStats(): array {
    $stats = [];
    $errors = [];
    $cmdTimeout = 5; // segundos para timeout de comandos
    
    // CPU - método principal
    try {
        // Implementación con timeout para wmic
        $cpuObtained = false;
        
        // Intentar con wmic primero con timeout
        $cmd = 'wmic cpu get loadpercentage /value';
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            // Configurar pipes a no-bloqueo
            stream_set_blocking($pipes[1], 0);
            
            // Esperar respuesta con timeout
            $output = '';
            $startTime = time();
            while (time() - $startTime < $cmdTimeout) {
                $output .= stream_get_contents($pipes[1]);
                // Si ya tenemos datos suficientes, salir
                if (strpos($output, 'LoadPercentage=') !== false) {
                    break;
                }
                usleep(100000); // 100ms
            }
            
            // Terminar el proceso si sigue corriendo
            $status = proc_get_status($process);
            if ($status['running']) {
                proc_terminate($process);
            }
            proc_close($process);
            
            if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                $stats['cpu_usage'] = (float)$matches[1];
                $this->logMessage("CPU obtenida con wmic: {$stats['cpu_usage']}%");
                $cpuObtained = true;
            }
        }
        
        // Método alternativo con PowerShell si wmic falla
        if (!$cpuObtained) {
            $cmd = 'powershell -command "(Get-WmiObject Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average"';
            $process = proc_open($cmd, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                // Configurar pipes a no-bloqueo
                stream_set_blocking($pipes[1], 0);
                
                // Esperar respuesta con timeout
                $output = '';
                $startTime = time();
                while (time() - $startTime < $cmdTimeout) {
                    $output .= stream_get_contents($pipes[1]);
                    if (!empty(trim($output))) {
                        break;
                    }
                    usleep(100000); // 100ms
                }
                
                // Terminar el proceso si sigue corriendo
                $status = proc_get_status($process);
                if ($status['running']) {
                    proc_terminate($process);
                }
                proc_close($process);
                
                $cpuUsage = trim($output);
                if (is_numeric($cpuUsage)) {
                    $stats['cpu_usage'] = (float)$cpuUsage;
                    $this->logMessage("CPU obtenida con PowerShell: {$stats['cpu_usage']}%");
                    $cpuObtained = true;
                }
            }
        }
        
        // Si ningún método funcionó, establecer valor por defecto
        if (!$cpuObtained) {
            $stats['cpu_usage'] = 0;
            $errors[] = "No se pudo obtener CPU con los métodos disponibles";
        }
    } catch (\Exception $e) {
        $stats['cpu_usage'] = 0;
        $errors[] = "Error CPU: " . $e->getMessage();
    }
    
    // Memoria - método principal
    try {
        $memoryObtained = false;
        
        // Intentar con PowerShell para mayor precisión
        $cmd = 'powershell -command "Get-WmiObject -Class Win32_OperatingSystem | ' .
               'Select-Object @{Name=\'TotalMemoryGB\';Expression={[math]::Round($_.TotalVisibleMemorySize/1MB, 2)}}, ' .
               '@{Name=\'FreeMemoryGB\';Expression={[math]::Round($_.FreePhysicalMemory/1MB, 2)}}, ' .
               '@{Name=\'UsedPercent\';Expression={[math]::Round(($_.TotalVisibleMemorySize - $_.FreePhysicalMemory) / $_.TotalVisibleMemorySize * 100, 2)}} | ' .
               'ConvertTo-Json"';
        
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            stream_set_blocking($pipes[1], 0);
            
            $output = '';
            $startTime = time();
            while (time() - $startTime < $cmdTimeout) {
                $output .= stream_get_contents($pipes[1]);
                if (strpos($output, 'UsedPercent') !== false) {
                    break;
                }
                usleep(100000); // 100ms
            }
            
            // Terminar el proceso si sigue corriendo
            $status = proc_get_status($process);
            if ($status['running']) {
                proc_terminate($process);
            }
            proc_close($process);
            
            $memInfo = json_decode($output, true);
            
            if (isset($memInfo['UsedPercent'])) {
                $stats['memory_usage'] = (float)$memInfo['UsedPercent'];
                $stats['total_memory_gb'] = (float)$memInfo['TotalMemoryGB'];
                $stats['free_memory_gb'] = (float)$memInfo['FreeMemoryGB'];
                $this->logMessage("Memoria obtenida con PowerShell: {$stats['memory_usage']}%");
                $memoryObtained = true;
            }
        }
        
        // Método alternativo con systeminfo
        if (!$memoryObtained) {
            $cmd = 'systeminfo | findstr /C:"Total Physical Memory" /C:"Available Physical Memory"';
            $process = proc_open($cmd, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                stream_set_blocking($pipes[1], 0);
                
                $output = '';
                $startTime = time();
                while (time() - $startTime < $cmdTimeout) {
                    $output .= stream_get_contents($pipes[1]);
                    if (strpos($output, 'Total Physical Memory') !== false && 
                        strpos($output, 'Available Physical Memory') !== false) {
                        break;
                    }
                    usleep(100000); // 100ms
                }
                
                // Terminar el proceso si sigue corriendo
                $status = proc_get_status($process);
                if ($status['running']) {
                    proc_terminate($process);
                }
                proc_close($process);
                
                $lines = explode("\n", $output);
                $totalMemory = 0;
                $availableMemory = 0;
                
                foreach ($lines as $line) {
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
                    $this->logMessage("Memoria obtenida con systeminfo: {$stats['memory_usage']}%");
                    $memoryObtained = true;
                }
            }
        }
        
        // Si ningún método funcionó, establecer valores por defecto
        if (!$memoryObtained) {
            $stats['memory_usage'] = 0;
            $stats['total_memory_mb'] = 0;
            $stats['available_memory_mb'] = 0;
            $errors[] = "No se pudo obtener memoria con los métodos disponibles";
        }
    } catch (\Exception $e) {
        $stats['memory_usage'] = 0;
        $errors[] = "Error memoria: " . $e->getMessage();
    }
    
    // Disco - método mejorado
    try {
        $diskStats = [];
        $diskObtained = false;
        
        // Intentar con PowerShell para mayor precisión
        $cmd = 'powershell -command "Get-WmiObject -Class Win32_LogicalDisk -Filter \'DriveType = 3\' | ' .
               'Select-Object DeviceID, @{Name=\'SizeGB\';Expression={[math]::Round($_.Size/1GB, 2)}}, ' .
               '@{Name=\'FreeGB\';Expression={[math]::Round($_.FreeSpace/1GB, 2)}}, ' .
               '@{Name=\'UsedPercent\';Expression={[math]::Round(($_.Size - $_.FreeSpace) / $_.Size * 100, 2)}} | ' .
               'ConvertTo-Json -Compress"';
        
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            stream_set_blocking($pipes[1], 0);
            
            $output = '';
            $startTime = time();
            while (time() - $startTime < $cmdTimeout) {
                $output .= stream_get_contents($pipes[1]);
                if (strpos($output, 'DeviceID') !== false) {
                    break;
                }
                usleep(100000); // 100ms
            }
            
            // Terminar el proceso si sigue corriendo
            $status = proc_get_status($process);
            if ($status['running']) {
                proc_terminate($process);
            }
            proc_close($process);
            
            $disks = json_decode($output, true);
            
            // Verificar si es un disco único o múltiples
            if (isset($disks['DeviceID'])) {
                // Es un solo disco
                $diskStats[$disks['DeviceID']] = [
                    'total_gb' => $disks['SizeGB'],
                    'free_gb' => $disks['FreeGB'],
                    'usage_percent' => $disks['UsedPercent']
                ];
                $diskObtained = true;
            } elseif (is_array($disks)) {
                // Son múltiples discos
                foreach ($disks as $disk) {
                    if (isset($disk['DeviceID'])) {
                        $diskStats[$disk['DeviceID']] = [
                            'total_gb' => $disk['SizeGB'],
                            'free_gb' => $disk['FreeGB'],
                            'usage_percent' => $disk['UsedPercent']
                        ];
                        $diskObtained = true;
                    }
                }
            }
        }
        
        // Si no se obtuvo información con PowerShell, intentar con wmic
        if (!$diskObtained) {
            $cmd = 'wmic logicaldisk get caption,size,freespace /format:csv';
            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
            
            $process = proc_open($cmd, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                stream_set_blocking($pipes[1], 0);
                
                $output = '';
                $startTime = time();
                while (time() - $startTime < $cmdTimeout) {
                    $output .= stream_get_contents($pipes[1]);
                    if (strpos($output, 'Caption,FreeSpace,Size') !== false) {
                        break;
                    }
                    usleep(100000); // 100ms
                }
                
                // Terminar el proceso si sigue corriendo
                $status = proc_get_status($process);
                if ($status['running']) {
                    proc_terminate($process);
                }
                proc_close($process);
                
                $lines = explode("\n", $output);
                
                foreach ($lines as $i => $line) {
                    if ($i > 0 && !empty(trim($line))) {
                        $parts = str_getcsv($line);
                        if (count($parts) >= 4) {
                            $drive = $parts[1];
                            $size = (float)$parts[3];
                            $freeSpace = (float)$parts[2];
                            
                            if ($size > 0) {
                                $usedPercentage = round((($size - $freeSpace) / $size) * 100, 2);
                                $diskStats[$drive] = [
                                    'total_gb' => round($size / (1024 * 1024 * 1024), 2),
                                    'free_gb' => round($freeSpace / (1024 * 1024 * 1024), 2),
                                    'usage_percent' => $usedPercentage
                                ];
                                $diskObtained = true;
                            }
                        }
                    }
                }
            }
        }
        
        $stats['disk_stats'] = $diskStats;
        
        if (!$diskObtained) {
            $errors[] = "No se pudo obtener información de discos";
        }
        
    } catch (\Exception $e) {
        $stats['disk_stats'] = [];
        $errors[] = "Error disco: " . $e->getMessage();
    }
    
    if (!empty($errors)) {
        $stats['errors'] = $errors;
        $this->logMessage("Errores en Windows Stats: " . implode("; ", $errors), true);
    }
    
    return $stats;
}
    
    /**
     * Obtiene estadísticas para sistemas macOS
     */
    private function getMacOSStats(): array {
        $stats = [];
        $errors = [];
        
        // CPU
        try {
            // Método para macOS usando top
            $cmd = "top -l 1 | grep -E '^CPU'";
            $output = trim(shell_exec($cmd));
            
            if (preg_match('/([0-9.]+)% idle/', $output, $matches)) {
                $idlePercent = (float)$matches[1];
                $stats['cpu_usage'] = round(100 - $idlePercent, 2);
            } else {
                // Método alternativo con vm_stat y sysctl
                $cmd = "sysctl -n hw.ncpu";
                $cpuCores = (int)trim(shell_exec($cmd));
                
                $cmd = "sysctl -n vm.loadavg";
                $loadAvg = trim(shell_exec($cmd));
                if (preg_match('/{ ([0-9.]+)/', $loadAvg, $matches)) {
                    $load = (float)$matches[1];
                    $stats['cpu_usage'] = round(($load / $cpuCores) * 100, 2);
                } else {
                    $stats['cpu_usage'] = 0;
                    $errors[] = "No se pudo obtener CPU en macOS";
                }
            }
        } catch (\Exception $e) {
            $stats['cpu_usage'] = 0;
            $errors[] = "Error CPU macOS: " . $e->getMessage();
        }
        
        // Memoria
        try {
            $cmd = "vm_stat";
            $output = shell_exec($cmd);
            
            // Parse vm_stat output
            $pageSize = 4096; // Default page size en macOS (4KB)
            preg_match('/page size of ([0-9]+) bytes/', $output, $pageSizeMatch);
            if (isset($pageSizeMatch[1])) {
                $pageSize = (int)$pageSizeMatch[1];
            }
            
            preg_match('/Pages free: *([0-9]+)\./', $output, $freeMatch);
            preg_match('/Pages active: *([0-9]+)\./', $output, $activeMatch);
            preg_match('/Pages inactive: *([0-9]+)\./', $output, $inactiveMatch);
            preg_match('/Pages speculative: *([0-9]+)\./', $output, $speculativeMatch);
            preg_match('/Pages wired down: *([0-9]+)\./', $output, $wiredMatch);
            
            $free = isset($freeMatch[1]) ? (int)$freeMatch[1] : 0;
            $active = isset($activeMatch[1]) ? (int)$activeMatch[1] : 0;
            $inactive = isset($inactiveMatch[1]) ? (int)$inactiveMatch[1] : 0;
            $speculative = isset($speculativeMatch[1]) ? (int)$speculativeMatch[1] : 0;
            $wired = isset($wiredMatch[1]) ? (int)$wiredMatch[1] : 0;
            
            // Get total physical memory
            $cmd = "sysctl -n hw.memsize";
            $totalMemoryBytes = (int)trim(shell_exec($cmd));
            $totalPages = $totalMemoryBytes / $pageSize;
            
            $usedPages = $active + $wired;
            $usedMemory = $usedPages * $pageSize;
            
            $stats['memory_usage'] = round(($usedMemory / $totalMemoryBytes) * 100, 2);
            $stats['total_memory_gb'] = round($totalMemoryBytes / (1024 * 1024 * 1024), 2);
            $stats['used_memory_gb'] = round($usedMemory / (1024 * 1024 * 1024), 2);
            $stats['free_memory_gb'] = round(($free * $pageSize) / (1024 * 1024 * 1024), 2);
            
        } catch (\Exception $e) {
            $stats['memory_usage'] = 0;
            $errors[] = "Error memoria macOS: " . $e->getMessage();
        }
        
        // Disco
        try {
            $cmd = "df -h";
            $output = [];
            exec($cmd, $output);
            
            $diskStats = [];
            foreach ($output as $i => $line) {
                if ($i > 0) { // Skip header
                    $parts = preg_split('/\s+/', trim($line));
                    if (count($parts) >= 5) {
                        $filesystem = $parts[0];
                        $size = $parts[1];
                        $used = $parts[2];
                        $avail = $parts[3];
                        $usedPercent = (int)str_replace('%', '', $parts[4]);
                        
                        // Filtrar solo discos físicos reales
                        if (strpos($filesystem, '/dev/') === 0) {
                            $diskStats[$filesystem] = [
                                'size' => $size,
                                'used' => $used,
                                'available' => $avail,
                                'usage_percent' => $usedPercent
                            ];
                        }
                    }
                }
            }
            
            $stats['disk_stats'] = $diskStats;
            
        } catch (\Exception $e) {
            $stats['disk_stats'] = [];
            $errors[] = "Error disco macOS: " . $e->getMessage();
        }
        
        // Procesos
        try {
            $cmd = "ps -ax | wc -l";
            $stats['process_count'] = (int)trim(shell_exec($cmd));
            
            $cmd = "who | wc -l";
            $stats['user_count'] = (int)trim(shell_exec($cmd));
            
        } catch (\Exception $e) {
            $stats['process_count'] = 0;
            $stats['user_count'] = 0;
            $errors[] = "Error procesos macOS: " . $e->getMessage();
        }
        
        if (!empty($errors)) {
            $stats['errors'] = $errors;
            $this->logMessage("Errores en macOS Stats: " . implode("; ", $errors), true);
        }
        
        return $stats;
    }
    
    /**
     * Obtiene estadísticas para sistemas Unix/Linux con métodos alternativos
     */
    private function getUnixStats(): array {
        $stats = [];
        $errors = [];
        
        // CPU - método múltiple
        try {
            $cpuUsage = null;
            
            // Método 1: usando top (más preciso)
            try {
                // Para diferentes variantes de top
                $cmdVariants = [
                    "top -bn1 | grep 'Cpu(s)' | awk '{print $2 + $4}'",
                    "top -bn1 | grep '%Cpu' | awk '{print $2 + $4}'",
                    "top -bn1 | head -3 | tail -1 | awk '{print $2 + $4}'"
                ];
                
                foreach ($cmdVariants as $cmd) {
                    $output = trim(shell_exec($cmd));
                    if (!empty($output) && is_numeric($output)) {
                        $cpuUsage = (float)$output;
                        $this->logMessage("CPU obtenida con top: {$cpuUsage}%");
                        break;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Error en método top: " . $e->getMessage();
            }
            
            // Método 2: usando /proc/stat si top falló
            if ($cpuUsage === null) {
                try {
                    // Medir CPU con dos lecturas separadas para mayor precisión
                    $stat1 = file('/proc/stat');
                    usleep(200000); // 200ms
                    $stat2 = file('/proc/stat');
                    
                    if ($stat1 && $stat2) {
                        $cpu1 = preg_split('/\s+/', $stat1[0]);
                        $cpu2 = preg_split('/\s+/', $stat2[0]);
                        
                        // Calcular diferencias
                        $total1 = $cpu1[1] + $cpu1[2] + $cpu1[3] + $cpu1[4] + $cpu1[5] + $cpu1[6] + $cpu1[7];
                        $total2 = $cpu2[1] + $cpu2[2] + $cpu2[3] + $cpu2[4] + $cpu2[5] + $cpu2[6] + $cpu2[7];
                        $idle1 = $cpu1[4];
                        $idle2 = $cpu2[4];
                        
                        $totalDiff = $total2 - $total1;
                        $idleDiff = $idle2 - $idle1;
                        
                        if ($totalDiff > 0) {
                            $cpuUsage = round((1 - ($idleDiff / $totalDiff)) * 100, 2);
                            $this->logMessage("CPU obtenida con /proc/stat: {$cpuUsage}%");
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error en método /proc/stat: " . $e->getMessage();
                }
            }
            
            // Método 3: Intentar con load average como último recurso
            if ($cpuUsage === null) {
                $load = sys_getloadavg();
                $cmd = "nproc";
                $cpuCores = trim(shell_exec($cmd));
                
                if (!empty($cpuCores) && is_numeric($cpuCores) && $cpuCores > 0) {
                    $cpuUsage = round(($load[0] / $cpuCores) * 100, 2);
                } else {
                    $cpuUsage = round($load[0] * 25, 2); // Estimación aproximada
                }
                $this->logMessage("CPU estimada con load average: {$cpuUsage}%");
            }
            
            $stats['cpu_usage'] = $cpuUsage;
            $stats['cpu_cores'] = (int)trim(shell_exec("nproc"));
            $stats['load_avg'] = sys_getloadavg();
            
        } catch (\Exception $e) {
            $stats['cpu_usage'] = 0;
            $errors[] = "Error general CPU: " . $e->getMessage();
        }
        
        // Memoria - múltiples métodos
        try {
            $memoryInfo = null;
            
            // Método 1: usando free
            try {
                $cmdVariants = [
                    "free | grep Mem | awk '{print $2,$3,$4,$5,$6,$7}'",
                    "free -m | grep Mem | awk '{print $2,$3}'"
                ];
                
                foreach ($cmdVariants as $cmd) {
                    $output = trim(shell_exec($cmd));
                    if (!empty($output)) {
                        $memInfo = explode(' ', $output);
                        if (count($memInfo) >= 2) {
                            $totalMemory = (int)$memInfo[0];
                            $usedMemory = (int)$memInfo[1];
                            
                            if ($totalMemory > 0) {
                                $memoryInfo = [
                                    'memory_usage' => round(($usedMemory / $totalMemory) * 100, 2),
                                    'total_memory' => $totalMemory,
                                    'used_memory' => $usedMemory
                                ];
                                $this->logMessage("Memoria obtenida con free: {$memoryInfo['memory_usage']}%");
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Error en método free: " . $e->getMessage();
            }
            
            // Método 2: usando /proc/meminfo
            if ($memoryInfo === null) {
                try {
                    if (is_readable('/proc/meminfo')) {
                        $content = file_get_contents('/proc/meminfo');
                        preg_match('/MemTotal:\s+(\d+)/i', $content, $matchTotal);
                        preg_match('/MemFree:\s+(\d+)/i', $content, $matchFree);
                        preg_match('/Buffers:\s+(\d+)/i', $content, $matchBuffers);
                        preg_match('/Cached:\s+(\d+)/i', $content, $matchCached);
                        
                        $total = isset($matchTotal[1]) ? (int)$matchTotal[1] : 0;
                        $free = isset($matchFree[1]) ? (int)$matchFree[1] : 0;
                        $buffers = isset($matchBuffers[1]) ? (int)$matchBuffers[1] : 0;
                        $cached = isset($matchCached[1]) ? (int)$matchCached[1] : 0;
                        
                        $used = $total - $free - $buffers - $cached;
                        
                        if ($total > 0) {
                            $memoryInfo = [
                                'memory_usage' => round(($used / $total) * 100, 2),
                                'total_memory' => $total,
                                'used_memory' => $used
                            ];
                            $this->logMessage("Memoria obtenida con /proc/meminfo: {$memoryInfo['memory_usage']}%");
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error en método /proc/meminfo: " . $e->getMessage();
                }
            }
            
            // Añadir la información de memoria a las estadísticas
            if ($memoryInfo !== null) {
                $stats['memory_usage'] = $memoryInfo['memory_usage'];
                $stats['total_memory_kb'] = $memoryInfo['total_memory'];
                $stats['used_memory_kb'] = $memoryInfo['used_memory'];
                
                // Añadir valores en GB para consistencia
                $stats['total_memory_gb'] = round($memoryInfo['total_memory'] / 1024 / 1024, 2);
                $stats['used_memory_gb'] = round($memoryInfo['used_memory'] / 1024 / 1024, 2);
            } else {
                // Si ningún método funcionó
                $stats['memory_usage'] = 0;
                $stats['total_memory_kb'] = 0;
                $stats['used_memory_kb'] = 0;
                $errors[] = "No se pudo obtener información de memoria";
            }
            
        } catch (\Exception $e) {
            $stats['memory_usage'] = 0;
            $errors[] = "Error general memoria: " . $e->getMessage();
        }
        
        // Disco - mejorado
        try {
            $cmd = "df -h | grep -v 'tmpfs\|cdrom\|udev\|none'";
            $output = [];
            exec($cmd, $output);
            
            $diskStats = [];
            
            foreach ($output as $i => $line) {
                if ($i > 0 || (strpos($line, 'Filesystem') === false)) { // Skip header if present
                    $parts = preg_split('/\s+/', trim($line));
                    if (count($parts) >= 5) {
                        $filesystem = $parts[0];
                        $size = $parts[1];
                        $used = $parts[2];
                        $avail = $parts[3];
                        $usedPercent = (int)str_replace('%', '', $parts[4]);
                        
                        // Filtrar solo discos físicos reales, no montajes virtuales
                        if (strpos($filesystem, '/dev/') === 0) {
                            $diskStats[$filesystem] = [
                                'size' => $size,
                                'used' => $used,
                                'available' => $avail,
                                'usage_percent' => $usedPercent
                            ];
                        }
                    }
                }
            }
            
            $stats['disk_stats'] = $diskStats;
            
        } catch (\Exception $e) {
            $stats['disk_stats'] = [];
            $errors[] = "Error disco: " . $e->getMessage();
        }
        
        // Información de procesos y usuarios
        try {
            $cmd = "ps -ef | wc -l";
            $stats['process_count'] = (int)trim(shell_exec($cmd)) - 1; // Restar el encabezado
            
            $cmd = "who | wc -l";
            $stats['user_count'] = (int)trim(shell_exec($cmd));
            
        } catch (\Exception $e) {
            $stats['process_count'] = 0;
            $stats['user_count'] = 0;
            $errors[] = "Error procesos: " . $e->getMessage();
        }
        
        // Tiempo de actividad del sistema
        try {
            $cmd = "uptime";
            $uptime = trim(shell_exec($cmd));
            if (!empty($uptime)) {
                $stats['uptime'] = $uptime;
            }
        } catch (\Exception $e) {
            $errors[] = "Error uptime: " . $e->getMessage();
        }
        
        if (!empty($errors)) {
            $stats['errors'] = $errors;
            $this->logMessage("Errores en Unix Stats: " . implode("; ", $errors), true);
        }
        
        return $stats;
    }

    /**
     * Registra las estadísticas actuales del servidor en InfluxDB
     * 
     * @return bool True si se registraron correctamente, false en caso contrario
     */
    public function recordStats(): bool {
        try {
            $stats = $this->getServerStats();
            
            // Crear punto de datos para InfluxDB
            $fields = [
                'cpu_usage' => $stats['cpu_usage'],
                'memory_usage' => $stats['memory_usage'],
                'elapsed_time' => $stats['elapsed_time']
            ];
            
            // Añadir campos opcionales si existen
            if (isset($stats['process_count'])) {
                $fields['process_count'] = $stats['process_count'];
            }
            
            if (isset($stats['user_count'])) {
                $fields['user_count'] = $stats['user_count'];
            }
            
            // Si hay estadísticas de disco, añadir el promedio de uso
            if (isset($stats['disk_stats']) && !empty($stats['disk_stats'])) {
                $diskUsageSum = 0;
                $diskCount = 0;
                
                foreach ($stats['disk_stats'] as $disk) {
                    if (isset($disk['usage_percent'])) {
                        $diskUsageSum += $disk['usage_percent'];
                        $diskCount++;
                    }
                }
                
                if ($diskCount > 0) {
                    $fields['avg_disk_usage'] = round($diskUsageSum / $diskCount, 2);
                }
            }
            
            // Registrar en InfluxDB
            $result = $this->influxClient->writeData(
                'server_monitoring',
                $fields,
                [
                    'test_id' => $this->testId,
                    'os_type' => $this->osType
                ]
            );
            
            if ($result) {
                $this->logMessage("Estadísticas del servidor registradas correctamente en InfluxDB");
                return true;
            } else {
                $this->logMessage("Error al registrar estadísticas del servidor en InfluxDB", true);
                return false;
            }
            
        } catch (\Exception $e) {
            $this->logMessage("Error al registrar estadísticas: " . $e->getMessage(), true);
            return false;
        }
    }

    /**
     * Calcula el tiempo transcurrido desde el inicio del monitoreo
     * 
     * @return float Tiempo transcurrido en segundos
     */
    private function getElapsedTime(): float {
        return microtime(true) - $this->startTime;
    }

    /**
     * Obtiene el uso máximo de CPU registrado
     * 
     * @return float Porcentaje máximo de uso de CPU
     */
    public function getMaxCpuUsage(): float {
        return $this->maxCpuUsage;
    }   

    /**
     * Obtiene el uso máximo de memoria registrado
     * 
     * @return float Porcentaje máximo de uso de memoria
     */
    public function getMaxMemoryUsage(): float {
        return $this->maxMemoryUsage;
    }
   
   public function getLastStats(): array {
       // Si lastStats está vacío, obtener estadísticas actuales
       if (empty($this->lastStats)) {
           return $this->getServerStats();
       }
       
       return $this->lastStats;
   }

    /**
     * Finaliza el monitoreo y registra el resumen
     * 
     * @param array $testSummary Resumen opcional de la prueba
     * @return bool True si se registró correctamente, false en caso contrario
     */
    public function finalize(array $testSummary = []): bool {
        try {
            $elapsedTime = $this->getElapsedTime();
            
            // Crear punto de datos final
            $fields = [
                'max_cpu_usage' => $this->maxCpuUsage,
                'max_memory_usage' => $this->maxMemoryUsage,
                'total_duration' => $elapsedTime,
                'status' => 'completed'
            ];
            
            // Añadir campos de resumen si se proporcionan
            if (!empty($testSummary)) {
                foreach ($testSummary as $key => $value) {
                    if (is_numeric($value)) {
                        $fields[$key] = $value;
                    }
                }
            }
            
            // Registrar en InfluxDB
            $result = $this->influxClient->writeData(
                'server_monitoring_summary',
                $fields,
                [
                    'test_id' => $this->testId,
                    'os_type' => $this->osType
                ]
            );
            
            if ($result) {
                $this->logMessage("Resumen de monitoreo registrado correctamente. Duración total: {$elapsedTime}s", true);
                return true;
            } else {
                $this->logMessage("Error al registrar resumen de monitoreo en InfluxDB", true);
                return false;
            }
            
        } catch (\Exception $e) {
            $this->logMessage("Error al finalizar monitoreo: " . $e->getMessage(), true);
            return false;
        }
    }

    /**
     * Establece la frecuencia de logging en segundos
     * 
     * @param int $seconds Segundos entre entradas de log
     * @return void
     */
    public function setLogFrequency(int $seconds): void {
        if ($seconds > 0) {
            $this->logFrequency = $seconds;
            $this->logMessage("Frecuencia de log establecida a {$seconds} segundos");
        }
    }
}