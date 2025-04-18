<?php
// PHP/escalated_load_test.php
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;
use Metrics\ServerMonitor;

// Configuración de la prueba
$baseUrl = isset($_GET['url']) ? $_GET['url'] : 'http://localhost/index.php';
$testId = uniqid('escalated_');
$stagesConfig = isset($_GET['stages']) ? $_GET['stages'] : '50,5,100|100,10,100|200,20,100|500,50,100';

// Procesar configuración de etapas (formato: solicitudes,concurrencia,intervalo|...)
$stages = [];
foreach (explode('|', $stagesConfig) as $stageConfig) {
    list($requests, $concurrency, $intervalMs) = explode(',', $stageConfig);
    $stages[] = [
        'requests' => (int)$requests,
        'concurrency' => (int)$concurrency,
        'intervalMs' => (int)$intervalMs
    ];
}

// Validación de configuración
if (empty($stages)) {
    die("Error: Configuración de etapas inválida.");
}

// Límites de seguridad
$maxRequestsAllowed = 10000;
$maxConcurrencyAllowed = 500;
$minIntervalMs = 10;

// Validar cada etapa
foreach ($stages as &$stage) {
    if ($stage['requests'] < 1) $stage['requests'] = 1;
    if ($stage['requests'] > $maxRequestsAllowed) $stage['requests'] = $maxRequestsAllowed;
    
    if ($stage['concurrency'] < 1) $stage['concurrency'] = 1;
    if ($stage['concurrency'] > $maxConcurrencyAllowed) $stage['concurrency'] = $maxConcurrencyAllowed;
    
    if ($stage['intervalMs'] < $minIntervalMs) $stage['intervalMs'] = $minIntervalMs;
}

// Preparar el cliente InfluxDB para métricas de la prueba
$influxClient = MetricsFactory::createInfluxDBClient();
if (!$influxClient->isConnected()) {
    die("Error: No se pudo conectar a InfluxDB para registrar la prueba.");
}

// Inicializar el monitor de servidor
$serverMonitor = new ServerMonitor($influxClient, $testId);

// Iniciar la prueba y registrar el inicio
$startTime = microtime(true);
$totalRequests = array_sum(array_column($stages, 'requests'));

// HTML para la interfaz de usuario
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Prueba de Carga Escalonada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: #f9f9f9; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .progress-container { height: 30px; background-color: #f3f3f3; border-radius: 5px; margin: 20px 0; }
        .progress-bar { height: 100%; width: 0%; background-color: #4CAF50; border-radius: 5px; text-align: center; line-height: 30px; color: white; transition: width 0.3s; }
        .stage-info { display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 15px; }
        .stage-item { flex: 1; min-width: 200px; margin-right: 10px; margin-bottom: 10px; padding: 10px; background: #fff; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .button { background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0; }
        .warning { color: #e74c3c; }
        .results-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .results-table th, .results-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .results-table th { background-color: #f2f2f2; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-top: 15px; }
        .stat-item { background: #fff; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-value { font-size: 24px; font-weight: bold; margin: 10px 0; }
        .stage-header { background: #eaeaea; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Prueba de Carga Escalonada</h1>
        <div class='card'>
            <p><strong>ID de prueba:</strong> {$testId}</p>
            <p><strong>URL objetivo:</strong> {$baseUrl}</p>
            <p><strong>Solicitudes totales programadas:</strong> {$totalRequests}</p>
            
            <h3>Etapas configuradas:</h3>
            <div class='stage-info'>";

// Mostrar información de cada etapa
foreach ($stages as $index => $stage) {
    echo "<div class='stage-item'>
            <strong>Etapa " . ($index + 1) . ":</strong>
            <ul>
                <li>Solicitudes: {$stage['requests']}</li>
                <li>Concurrencia: {$stage['concurrency']}</li>
                <li>Intervalo: {$stage['intervalMs']}ms</li>
            </ul>
        </div>";
}

echo "  </div>
        </div>
        
        <div class='card'>
            <h2>Progreso de la prueba</h2>
            <div class='progress-container'>
                <div id='progress-bar' class='progress-bar'>0%</div>
            </div>
            <div id='current-stage-info'>Preparando prueba...</div>
            <div id='current-metrics'>
                <p>Solicitudes procesadas: <span id='requests-processed'>0</span> de {$totalRequests}</p>
                <p>Tiempo transcurrido: <span id='elapsed-time'>0s</span></p>
            </div>
        </div>
        
        <div id='stage-results'></div>
        <div id='final-results' style='display:none;'></div>
    </div>

    <script>
        function updateProgress(percent) {
            document.getElementById('progress-bar').style.width = percent + '%';
            document.getElementById('progress-bar').innerText = percent + '%';
        }
        
        function updateStageInfo(stageNum, total, processed, avgTime) {
            document.getElementById('current-stage-info').innerHTML = 
                `<h3>Ejecutando Etapa ${stageNum} de ${total}</h3>`;
        }
        
        function updateMetrics(processed, elapsedTime) {
            document.getElementById('requests-processed').innerText = processed;
            document.getElementById('elapsed-time').innerText = elapsedTime;
        }
        
        function addStageResults(stageNum, results) {
            const resultDiv = document.createElement('div');
            resultDiv.className = 'card';
            resultDiv.innerHTML = results;
            document.getElementById('stage-results').appendChild(resultDiv);
        }
        
        function showFinalResults(results) {
            document.getElementById('final-results').innerHTML = results;
            document.getElementById('final-results').style.display = 'block';
        }
    </script>
";

flush();

// Función para realizar una solicitud HTTP
function makeRequest($url, $testId, $requestId, $stageNum) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    // Agregamos parámetros para identificar que es una prueba de carga
    $queryParams = "loadtest={$testId}&request={$requestId}&stage={$stageNum}";
    curl_setopt($ch, CURLOPT_URL, $url . (strpos($url, '?') === false ? '?' : '&') . $queryParams);
    
    // Medir el tiempo de respuesta
    $start = microtime(true);
    $response = curl_exec($ch);
    $end = microtime(true);
    
    // Obtener info de la respuesta
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    
    curl_close($ch);
    
    return [
        'request_id' => $requestId,
        'stage' => $stageNum,
        'http_code' => $httpCode,
        'response_time' => ($end - $start) * 1000, // en ms
        'curl_time' => $totalTime * 1000, // en ms
        'content_length' => $contentLength,
        'success' => ($httpCode >= 200 && $httpCode < 300)
    ];
}

// Registrar inicio de la prueba
$influxClient->writeData(
    'escalated_load_test', 
    [
        'status' => 1,
        'total_requests' => $totalRequests,
        'stages' => count($stages)
    ],
    [
        'test_id' => $testId,
        'test_type' => 'start',
        'target_url' => $baseUrl
    ]
);

// Variables para estadísticas globales
$allResults = [];
$globalTotalSuccess = 0;
$globalTotalFailure = 0;
$globalTotalTime = 0;
$requestsProcessed = 0;

// Ejecutar cada etapa
foreach ($stages as $stageIndex => $stage) {
    $stageNum = $stageIndex + 1;
    $numRequests = $stage['requests'];
    $concurrency = $stage['concurrency'];
    $intervalMs = $stage['intervalMs'];
    
    // Verificar condiciones del servidor antes de comenzar la etapa
    $serverStats = $serverMonitor->getServerStats();
    $cpuUsage = $serverStats['cpu_usage'] ?? 0;
    $memoryUsage = $serverStats['memory_usage'] ?? 0;
    
    // Si la carga del servidor es demasiado alta, podríamos abortar o pausar
    if ($cpuUsage > 95 || $memoryUsage > 95) {
        $warningMessage = "<div class='warning'>
            <h3>¡Advertencia! Recursos del servidor críticos</h3>
            <p>CPU: {$cpuUsage}%, Memoria: {$memoryUsage}%</p>
            <p>La prueba podría degradar el rendimiento del servidor. Considere reducir la carga o abortar.</p>
        </div>";
        
        echo $warningMessage;
        flush();
        
        // Opcionalmente, podríamos abortar aquí
        // break;
        
        // O reducir la concurrencia de esta etapa
        $concurrency = max(1, floor($concurrency * 0.5));
        echo "<p>Reduciendo concurrencia a {$concurrency} para proteger el servidor.</p>";
        flush();
    }
    
    // Registrar inicio de etapa
    $stageStartTime = microtime(true);
    $influxClient->writeData(
        'escalated_load_test_stage', 
        [
            'status' => 1,
            'stage_num' => $stageNum,
            'requests' => $numRequests,
            'concurrency' => $concurrency,
            'interval_ms' => $intervalMs
        ],
        [
            'test_id' => $testId,
            'stage' => (string)$stageNum,
            'target_url' => $baseUrl
        ]
    );
    
    // Actualizar UI
    echo "<script>
        updateStageInfo({$stageNum}, " . count($stages) . ", 0, 0);
    </script>";
    flush();
    
    // Variables para estadísticas de etapa
    $stageResults = [];
    $stageTotalSuccess = 0;
    $stageTotalFailure = 0;
    $stageTotalTime = 0;
    $stageRequestsProcessed = 0;
    
    // Loop de la etapa
    for ($i = 0; $i < $numRequests; $i += $concurrency) {
        $batch = [];
        $batchSize = min($concurrency, $numRequests - $i);
        
        // Crear lotes de solicitudes para simular concurrencia
        for ($j = 0; $j < $batchSize; $j++) {
            $requestId = $requestsProcessed + 1;
            $result = makeRequest($baseUrl, $testId, $requestId, $stageNum);
            $batch[] = $result;
            
            // Procesar y registrar los resultados
            $stageResults[] = $result;
            $allResults[] = $result;
            
            if ($result['success']) {
                $stageTotalSuccess++;
                $globalTotalSuccess++;
            } else {
                $stageTotalFailure++;
                $globalTotalFailure++;
            }
            
            $stageTotalTime += $result['response_time'];
            $globalTotalTime += $result['response_time'];
            $requestsProcessed++;
            $stageRequestsProcessed++;
            
            // Registrar cada solicitud en InfluxDB
            $influxClient->writeData(
                'escalated_request_metrics',
                [
                    'response_time_ms' => $result['response_time'],
                    'curl_time_ms' => $result['curl_time'],
                    'http_code' => $result['http_code'],
                    'content_length' => $result['content_length'],
                    'success' => $result['success'] ? 1 : 0
                ],
                [
                    'test_id' => $testId,
                    'stage' => (string)$stageNum,
                    'request_id' => (string)$result['request_id'],
                    'target_url' => $baseUrl
                ]
            );
        }
        
        // Monitoreo periódico del servidor
        if ($i % 20 == 0 || $i + $batchSize >= $numRequests) {
            $serverMonitor->recordStats();
        }
        
        // Actualizar progreso
        $totalProgress = round(($requestsProcessed / $totalRequests) * 100);
        $elapsedTime = number_format(microtime(true) - $startTime, 1) . 's';
        
        echo "<script>
            updateProgress({$totalProgress});
            updateMetrics({$requestsProcessed}, '{$elapsedTime}');
        </script>";
        flush();
        
        // Pausa para controlar la tasa de solicitudes
        if ($i + $batchSize < $numRequests) {
            usleep($intervalMs * 1000); // convertir ms a microsegundos
        }
    }
    
    // Calcular estadísticas de la etapa
    $stageEndTime = microtime(true);
    $stageDuration = $stageEndTime - $stageStartTime;
    $stageAvgResponseTime = $stageTotalTime / count($stageResults);
    
    // Ordenar resultados por tiempo de respuesta para calcular percentiles
    usort($stageResults, function($a, $b) {
        return $a['response_time'] <=> $b['response_time'];
    });
    
    $p50Index = floor(count($stageResults) * 0.5);
    $p90Index = floor(count($stageResults) * 0.9);
    $p95Index = floor(count($stageResults) * 0.95);
    $p99Index = floor(count($stageResults) * 0.99);
    
    $p50 = $stageResults[$p50Index]['response_time'];
    $p90 = $stageResults[$p90Index]['response_time'];
    $p95 = $stageResults[$p95Index]['response_time'];
    $p99 = $stageResults[$p99Index]['response_time'];
    
    // Registrar resultados de etapa en InfluxDB
    $influxClient->writeData(
        'escalated_load_test_stage', 
        [
            'status' => 2,
            'requests' => $numRequests,
            'successful_requests' => $stageTotalSuccess,
            'failed_requests' => $stageTotalFailure,
            'stage_duration_sec' => $stageDuration,
            'avg_response_time_ms' => $stageAvgResponseTime,
            'p50_response_time_ms' => $p50,
            'p90_response_time_ms' => $p90,
            'p95_response_time_ms' => $p95,
            'p99_response_time_ms' => $p99
        ],
        [
            'test_id' => $testId,
            'stage' => (string)$stageNum,
            'target_url' => $baseUrl
        ]
    );
    
    // Mostrar resultados de la etapa
    $stageResultsHTML = "
        <div class='stage-header'>
            <h2>Resultados de la Etapa {$stageNum}</h2>
        </div>
        <div class='stat-grid'>
            <div class='stat-item'>
                <h4>Concurrencia</h4>
                <div class='stat-value'>{$concurrency}</div>
            </div>
            <div class='stat-item'>
                <h4>Solicitudes</h4>
                <div class='stat-value'>{$numRequests}</div>
            </div>
            <div class='stat-item'>
                <h4>Duración</h4>
                <div class='stat-value'>" . number_format($stageDuration, 2) . " s</div>
            </div>
            <div class='stat-item'>
                <h4>Tasa de éxito</h4>
                <div class='stat-value'>" . number_format(($stageTotalSuccess / $numRequests) * 100, 1) . "%</div>
            </div>
            <div class='stat-item'>
                <h4>Tiempo Promedio</h4>
                <div class='stat-value'>" . number_format($stageAvgResponseTime, 2) . " ms</div>
            </div>
            <div class='stat-item'>
                <h4>Mediana (P50)</h4>
                <div class='stat-value'>" . number_format($p50, 2) . " ms</div>
            </div>
            <div class='stat-item'>
                <h4>P90</h4>
                <div class='stat-value'>" . number_format($p90, 2) . " ms</div>
            </div>
            <div class='stat-item'>
                <h4>P95</h4>
                <div class='stat-value'>" . number_format($p95, 2) . " ms</div>
            </div>
            <div class='stat-item'>
                <h4>P99</h4>
                <div class='stat-value'>" . number_format($p99, 2) . " ms</div>
            </div>
        </div>
    ";
    
    echo "<script>
        addStageResults({$stageNum}, `{$stageResultsHTML}`);
    </script>";
    flush();
    
    // Verificar si debemos continuar con la siguiente etapa
    $serverStats = $serverMonitor->getServerStats();
    if (($serverStats['cpu_usage'] ?? 0) > 98 || ($serverStats['memory_usage'] ?? 0) > 98) {
        echo "<div class='warning'>
            <h3>¡Prueba interrumpida! Recursos del servidor críticos</h3>
            <p>CPU: {$serverStats['cpu_usage']}%, Memoria: {$serverStats['memory_usage']}%</p>
            <p>La prueba se ha detenido para evitar degradar el rendimiento del servidor.</p>
        </div>";
        flush();
        break;
    }
}

// Calcular estadísticas globales
$endTime = microtime(true);
$testDuration = $endTime - $startTime;
$avgResponseTime = count($allResults) > 0 ? $globalTotalTime / count($allResults) : 0;

// Si hay resultados, calcular percentiles globales
if (count($allResults) > 0) {
    usort($allResults, function($a, $b) {
        return $a['response_time'] <=> $b['response_time'];
    });
    
    $p50Index = floor(count($allResults) * 0.5);
    $p90Index = floor(count($allResults) * 0.9);
    $p95Index = floor(count($allResults) * 0.95);
    $p99Index = floor(count($allResults) * 0.99);
    
    $p50 = $allResults[$p50Index]['response_time'];
    $p90 = $allResults[$p90Index]['response_time'];
    $p95 = $allResults[$p95Index]['response_time'];
    $p99 = $allResults[$p99Index]['response_time'];
} else {
    $p50 = $p90 = $p95 = $p99 = 0;
}

// Registrar resultados finales en InfluxDB
$influxClient->writeData(
    'escalated_load_test', 
    [
        'status' => 2,
        'total_requests' => $requestsProcessed,
        'successful_requests' => $globalTotalSuccess,
        'failed_requests' => $globalTotalFailure,
        'test_duration_sec' => $testDuration,
        'avg_response_time_ms' => $avgResponseTime,
        'p50_response_time_ms' => $p50,
        'p90_response_time_ms' => $p90,
        'p95_response_time_ms' => $p95,
        'p99_response_time_ms' => $p99
    ],
    [
        'test_id' => $testId,
        'test_type' => 'end',
        'target_url' => $baseUrl
    ]
);

// Mostrar resultados finales
$finalResultsHTML = "
<div class='card'>
    <h2>Resultados Finales de la Prueba</h2>
    <div class='stat-grid'>
        <div class='stat-item'>
            <h4>Test ID</h4>
            <div class='stat-value'>{$testId}</div>
        </div>
        <div class='stat-item'>
            <h4>Duración Total</h4>
            <div class='stat-value'>" . number_format($testDuration, 2) . " s</div>
        </div>
        <div class='stat-item'>
            <h4>Solicitudes Completadas</h4>
            <div class='stat-value'>{$requestsProcessed}</div>
        </div>
        <div class='stat-item'>
            <h4>Tasa de Éxito</h4>
            <div class='stat-value'>" . number_format(($globalTotalSuccess / max(1, $requestsProcessed)) * 100, 1) . "%</div>
        </div>
        <div class='stat-item'>
            <h4>Tiempo Promedio</h4>
            <div class='stat-value'>" . number_format($avgResponseTime, 2) . " ms</div>
        </div>
        <div class='stat-item'>
            <h4>Mediana (P50)</h4>
            <div class='stat-value'>" . number_format($p50, 2) . " ms</div>
        </div>
        <div class='stat-item'>
            <h4>P90</h4>
            <div class='stat-value'>" . number_format($p90, 2) . " ms</div>
        </div>
        <div class='stat-item'>
            <h4>P95</h4>
            <div class='stat-value'>" . number_format($p95, 2) . " ms</div>
        </div>
        <div class='stat-item'>
            <h4>P99</h4>
            <div class='stat-value'>" . number_format($p99, 2) . " ms</div>
        </div>
    </div>
    
    <h3>Recursos del Servidor (Máximos registrados)</h3>
    <div class='stat-grid'>
        <div class='stat-item'>
            <h4>CPU Máxima</h4>
            <div class='stat-value'>" . number_format($serverMonitor->getMaxCpuUsage(), 1) . "%</div>
        </div>
        <div class='stat-item'>
            <h4>Memoria Máxima</h4>
            <div class='stat-value'>" . number_format($serverMonitor->getMaxMemoryUsage(), 1) . "%</div>
        </div>
    </div>
    
    <div style='margin-top: 20px;'>
        <a href='test_report.php?test_id={$testId}' target='_blank' class='button'>Ver Informe Detallado</a>
        <a href='escalated_load_test.php' class='button' style='background-color: #3498db;'>Nueva Prueba</a>
    </div>
</div>
";

echo "<script>
    updateProgress(100);
    updateMetrics({$requestsProcessed}, '" . number_format($testDuration, 1) . "s');
    showFinalResults(`{$finalResultsHTML}`);
</script>";

?>
</body>
</html>