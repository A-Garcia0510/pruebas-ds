<?php
// PHP/test_load.php
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

// Configuración de la prueba
$numRequests = isset($_GET['requests']) ? (int)$_GET['requests'] : 100;
$concurrency = isset($_GET['concurrency']) ? (int)$_GET['concurrency'] : 10;
$intervalMs = isset($_GET['interval']) ? (int)$_GET['interval'] : 100;
$targetUrl = isset($_GET['url']) ? $_GET['url'] : 'http://localhost/index.php';
$testId = uniqid('loadtest_');

// Validación básica
if ($numRequests < 1) $numRequests = 1;
if ($concurrency < 1) $concurrency = 1;
if ($concurrency > 50) $concurrency = 50; // Límite de concurrencia para proteger el servidor
if ($intervalMs < 10) $intervalMs = 10;

// Preparar el cliente InfluxDB para métricas de la prueba
$influxClient = MetricsFactory::createInfluxDBClient();
if (!$influxClient->isConnected()) {
    die("Error: No se pudo conectar a InfluxDB para registrar la prueba.");
}

// Iniciar la prueba y registrar el inicio
$startTime = microtime(true);

echo "<h1>Prueba de Carga</h1>";
echo "<p>Iniciando prueba con el ID: <strong>{$testId}</strong></p>";
echo "<p>URL objetivo: <strong>{$targetUrl}</strong></p>";
echo "<p>Solicitudes totales: <strong>{$numRequests}</strong></p>";
echo "<p>Concurrencia: <strong>{$concurrency}</strong></p>";
echo "<p>Intervalo entre solicitudes: <strong>{$intervalMs}ms</strong></p>";

// Registrar inicio de la prueba
$influxClient->writeData(
    'load_test', 
    [
        'status' => 1,
        'total_requests' => $numRequests,
        'concurrency' => $concurrency,
        'interval_ms' => $intervalMs
    ],
    [
        'test_id' => $testId,
        'test_type' => 'start',
        'target_url' => $targetUrl
    ]
);

// Función para realizar una solicitud HTTP
function makeRequest($url, $testId, $requestId) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    // Agregamos un parámetro para identificar que es una prueba de carga
    curl_setopt($ch, CURLOPT_URL, $url . (strpos($url, '?') === false ? '?' : '&') . "loadtest={$testId}&request={$requestId}");
    
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
    
    // Extraer cuerpo de la respuesta
    $body = substr($response, $header_size);
    
    return [
        'request_id' => $requestId,
        'http_code' => $httpCode,
        'response_time' => ($end - $start) * 1000, // en ms
        'curl_time' => $totalTime * 1000, // en ms
        'content_length' => $contentLength,
        'success' => ($httpCode >= 200 && $httpCode < 300)
    ];
}

// Iniciar la prueba
$results = [];
$totalSuccess = 0;
$totalFailure = 0;
$totalTime = 0;

echo "<div id='progress' style='height: 30px; background-color: #f3f3f3; border-radius: 5px; margin: 20px 0;'>";
echo "<div id='progress-bar' style='height: 100%; width: 0%; background-color: #4CAF50; border-radius: 5px; text-align: center; line-height: 30px; color: white;'>0%</div>";
echo "</div>";

echo "<script>
function updateProgress(percent) {
    document.getElementById('progress-bar').style.width = percent + '%';
    document.getElementById('progress-bar').innerText = percent + '%';
}
</script>";

flush();

// Loop principal de la prueba
for ($i = 0; $i < $numRequests; $i += $concurrency) {
    $batch = [];
    $batchSize = min($concurrency, $numRequests - $i);
    
    // Crear lotes de solicitudes para simular concurrencia
    for ($j = 0; $j < $batchSize; $j++) {
        $requestId = $i + $j + 1;
        $batch[] = makeRequest($targetUrl, $testId, $requestId);
        
        // Procesar y registrar los resultados
        $lastResult = end($batch);
        $results[] = $lastResult;
        
        if ($lastResult['success']) {
            $totalSuccess++;
        } else {
            $totalFailure++;
        }
        $totalTime += $lastResult['response_time'];
        
        // Registrar cada solicitud en InfluxDB
        $influxClient->writeData(
            'request_metrics',
            [
                'response_time_ms' => $lastResult['response_time'],
                'curl_time_ms' => $lastResult['curl_time'],
                'http_code' => $lastResult['http_code'],
                'content_length' => $lastResult['content_length'],
                'success' => $lastResult['success'] ? 1 : 0
            ],
            [
                'test_id' => $testId,
                'request_id' => (string)$lastResult['request_id'],
                'target_url' => $targetUrl
            ]
        );
        
        // Actualizar progreso
        $progress = round(($i + $j + 1) / $numRequests * 100);
        echo "<script>updateProgress($progress);</script>";
        flush();
    }
    
    // Pausa para controlar la tasa de solicitudes
    if ($i + $batchSize < $numRequests) {
        usleep($intervalMs * 1000); // convertir ms a microsegundos
    }
}

// Calcular estadísticas
$endTime = microtime(true);
$testDuration = $endTime - $startTime;
$avgResponseTime = $totalTime / count($results);

// Ordenar resultados por tiempo de respuesta para calcular percentiles
usort($results, function($a, $b) {
    return $a['response_time'] <=> $b['response_time'];
});

$p50Index = floor(count($results) * 0.5);
$p90Index = floor(count($results) * 0.9);
$p95Index = floor(count($results) * 0.95);
$p99Index = floor(count($results) * 0.99);

$p50 = $results[$p50Index]['response_time'];
$p90 = $results[$p90Index]['response_time'];
$p95 = $results[$p95Index]['response_time'];
$p99 = $results[$p99Index]['response_time'];

// Registrar resultados finales en InfluxDB
$influxClient->writeData(
    'load_test', 
    [
        'status' => 2,
        'total_requests' => $numRequests,
        'successful_requests' => $totalSuccess,
        'failed_requests' => $totalFailure,
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
        'target_url' => $targetUrl
    ]
);

// Mostrar resultados
echo "<h2>Resultados de la Prueba</h2>";
echo "<div style='background-color: #f9f9f9; padding: 20px; border-radius: 5px;'>";
echo "<p><strong>Test ID:</strong> {$testId}</p>";
echo "<p><strong>Duración total:</strong> " . number_format($testDuration, 2) . " segundos</p>";
echo "<p><strong>Solicitudes exitosas:</strong> {$totalSuccess} de {$numRequests} (" . number_format(($totalSuccess / $numRequests) * 100, 2) . "%)</p>";
echo "<p><strong>Solicitudes fallidas:</strong> {$totalFailure}</p>";
echo "<p><strong>Tiempo promedio de respuesta:</strong> " . number_format($avgResponseTime, 2) . " ms</p>";
echo "<h3>Percentiles de Tiempo de Respuesta:</h3>";
echo "<ul>";
echo "<li><strong>P50 (Mediana):</strong> " . number_format($p50, 2) . " ms</li>";
echo "<li><strong>P90:</strong> " . number_format($p90, 2) . " ms</li>";
echo "<li><strong>P95:</strong> " . number_format($p95, 2) . " ms</li>";
echo "<li><strong>P99:</strong> " . number_format($p99, 2) . " ms</li>";
echo "</ul>";
echo "</div>";

// Enlace para ver gráficos
echo "<h2>Visualización de Métricas</h2>";
echo "<p>Las métricas de esta prueba de carga se han registrado en InfluxDB con el ID <strong>{$testId}</strong>.</p>";
echo "<p>Puedes visualizar estos datos en la interfaz de InfluxDB o mediante herramientas de visualización como Grafana.</p>";
echo "<p><a href='test_report.php?test_id={$testId}' target='_blank' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Ver Informe Detallado</a></p>";