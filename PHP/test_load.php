<?php
// PHP/test_load.php - Versión optimizada con escritura en InfluxDB
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

// Configuración de ejecución
set_time_limit(0);
ignore_user_abort(true);

// Control del formulario
$runTest = isset($_POST['submit']);
$formSubmitted = isset($_POST['submitted']) && $_POST['submitted'] === 'true';

// Parámetros de la prueba con sanitización
$config = [
    'requests' => filter_var(
        $formSubmitted 
            ? $_POST['requests'] 
            : (isset($_GET['requests']) ? $_GET['requests'] : 100), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 50000, 'default' => 100]]
    ),
    'concurrency' => filter_var(
        $formSubmitted 
            ? $_POST['concurrency'] 
            : (isset($_GET['concurrency']) ? $_GET['concurrency'] : 10), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 500, 'default' => 10]]
    ),
    'interval' => filter_var(
        $formSubmitted 
            ? $_POST['interval'] 
            : (isset($_GET['interval']) ? $_GET['interval'] : 100), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 5, 'max_range' => 5000, 'default' => 100]]
    ),
    'url' => filter_var(
        $formSubmitted 
            ? $_POST['url'] 
            : (isset($_GET['url']) ? $_GET['url'] : 'http://localhost/pruebas-ds/index.php'), 
        FILTER_VALIDATE_URL
    )
];

$testId = uniqid('loadtest_');

// Plantilla HTML
$htmlTemplate = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Carga Rápida</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 0 20px; }
        .panel { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .progress-bar { height: 25px; background: #28a745; transition: width 0.3s ease; border-radius: 4px; }
        .form-group { margin-bottom: 15px; }
        input[type="number"] { width: 120px; padding: 5px; }
        .btn { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>🔧 Prueba de Carga</h1>
    {%form%}
    {%results%}
</body>
</html>
HTML;

// Mostrar formulario o resultados
if (!$runTest) {
    $formContent = <<<FORM
    <div class="panel">
        <form method="post">
            <input type="hidden" name="submitted" value="true">
            
            <div class="form-group">
                <label>URL Objetivo:</label>
                <input type="url" name="url" value="{$config['url']}" required style="width:100%; padding:8px;">
            </div>

            <div class="form-group">
                <label>Solicitudes Totales:</label>
                <input type="number" name="requests" value="{$config['requests']}" min="1" max="50000" required>
            </div>

            <div class="form-group">
                <label>Concurrencia:</label>
                <input type="number" name="concurrency" value="{$config['concurrency']}" min="1" max="500" required>
            </div>
            
            <div class="form-group">
                <label>Intervalo (ms):</label>
                <input type="number" name="interval" value="{$config['interval']}" min="5" max="5000" required>
            </div>

            <button type="submit" name="submit" class="btn">Iniciar Prueba</button>
        </form>
    </div>
FORM;

    echo str_replace('{%form%}', $formContent, $htmlTemplate);
} else {
    // Inicializar prueba
    $influxClient = MetricsFactory::createInfluxDBClient();
    
    // Verificar conexión con InfluxDB
    if (!$influxClient->isConnected()) {
        $errorMessage = "Error: No se pudo conectar a InfluxDB. " . $influxClient->getLastError();
        $errorHtml = "<div class='error'>$errorMessage</div>";
        echo str_replace(['{%form%}', '{%results%}'], [$errorHtml, ''], $htmlTemplate);
        exit;
    }
    
    // Registrar el inicio de la prueba en InfluxDB
    $startSuccess = $influxClient->writeData('load_test', [
        'test_type' => 'start',
        'total_requests' => $config['requests'],
        'concurrency' => $config['concurrency'],
        'interval_ms' => $config['interval']
    ], [
        'test_id' => $testId,
        'target_url' => $config['url']
    ]);
    
    if (!$startSuccess) {
        $errorMessage = "Error al iniciar el registro de la prueba: " . $influxClient->getLastError();
        $errorHtml = "<div class='error'>$errorMessage</div>";
        echo str_replace(['{%form%}', '{%results%}'], [$errorHtml, ''], $htmlTemplate);
        exit;
    }
    
    // Contenido inicial de resultados
    $resultsContent = <<<RESULTS
    <div class="panel">
        <h2>🚀 Prueba en Progreso</h2>
        <div><strong>ID:</strong> $testId</div>
        <div><strong>URL:</strong> {$config['url']}</div>
        <div style="margin:15px 0;">
            <div class="progress-bar" style="width:0%" id="progressBar">0%</div>
        </div>
        <div id="liveStats" class="stats"></div>
    </div>
RESULTS;

    echo str_replace(['{%form%}', '{%results%}'], ['', $resultsContent], $htmlTemplate);
    flush();

    // Función optimizada para requests
    $makeRequest = function($url, $testId, $requestId) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$url?loadtest=$testId&request=$requestId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HEADER => true
        ]);
        
        $start = microtime(true);
        $response = curl_exec($ch);
        $end = microtime(true);
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentLength = strlen($body);
        
        curl_close($ch);
        
        return [
            'time' => round(($end - $start) * 1000, 2),
            'success' => $httpCode >= 200 && $httpCode < 300,
            'code' => $httpCode,
            'content_length' => $contentLength
        ];
    };

    // Ejecución de la prueba
    $stats = [
        'total' => 0,
        'success' => 0,
        'times' => [],
        'codes' => []
    ];

    $testStartTime = microtime(true);
    
    for ($i = 0; $i < $config['requests']; $i += $config['concurrency']) {
        $batchSize = min($config['concurrency'], $config['requests'] - $i);
        $batch = [];
        
        // Ejecutar batch
        for ($j = 0; $j < $batchSize; $j++) {
            $requestId = $i + $j + 1;
            $result = $makeRequest($config['url'], $testId, $requestId);
            $batch[] = $result;
            
            // Guardar resultado en InfluxDB
            $influxClient->writeData('request_metrics', [
                'response_time_ms' => $result['time'],
                'success' => $result['success'] ? 1 : 0,
                'http_code' => $result['code'],
                'content_length' => $result['content_length']
            ], [
                'test_id' => $testId,
                'request_id' => (string)$requestId
            ]);
        }
        
        // Procesar resultados
        foreach ($batch as $result) {
            $stats['total']++;
            if ($result['success']) $stats['success']++;
            $stats['times'][] = $result['time'];
            @$stats['codes'][$result['code']]++;
        }
        
        // Actualizar interfaz
        $progress = round(($i + $batchSize) / $config['requests'] * 100);
        $avgTime = round(array_sum($stats['times']) / count($stats['times']) ?? 0);
        $rps = round(count($stats['times']) / (microtime(true) - $testStartTime), 1);
        
        $liveStats = <<<JS
        <script>
            document.getElementById('progressBar').style.width = '$progress%';
            document.getElementById('progressBar').innerHTML = '$progress%';
            document.getElementById('liveStats').innerHTML = `
                <div class="panel">
                    <div>📊 Progreso: $progress%</div>
                    <div>⏱ Tiempo Promedio: {$avgTime}ms</div>
                    <div>⚡ Velocidad: {$rps} req/s</div>
                    <div>✅ Éxitos: {$stats['success']}/{$stats['total']}</div>
                </div>
            `;
        </script>
JS;
        
        echo $liveStats;
        flush();
        
        // Intervalo entre batches
        usleep($config['interval'] * 1000);
    }

    // Calcular estadísticas finales
    $testDuration = microtime(true) - $testStartTime;
    $successRate = round(($stats['success'] / $config['requests']) * 100, 1);
    $avgTime = round(array_sum($stats['times']) / count($stats['times']));
    
    // Calcular percentiles
    sort($stats['times']);
    $totalTimes = count($stats['times']);
    $p50 = $stats['times'][floor($totalTimes * 0.5)];
    $p90 = $stats['times'][floor($totalTimes * 0.9)];
    $p95 = $stats['times'][floor($totalTimes * 0.95)];
    $p99 = $stats['times'][floor($totalTimes * 0.99)];
    
    // Registrar el fin de la prueba en InfluxDB
    $endSuccess = $influxClient->writeData('load_test', [
        'test_type' => 'end',
        'total_requests' => $stats['total'],
        'successful_requests' => $stats['success'],
        'failed_requests' => $stats['total'] - $stats['success'],
        'avg_response_time_ms' => $avgTime,
        'p50_response_time_ms' => $p50,
        'p90_response_time_ms' => $p90,
        'p95_response_time_ms' => $p95,
        'p99_response_time_ms' => $p99,
        'test_duration_sec' => $testDuration
    ], [
        'test_id' => $testId,
        'target_url' => $config['url']
    ]);
    
    // Codigo para mostrar distribución de códigos HTTP en la UI
    $codeDistribution = array_map(function($code, $count) use ($config) {
        return "<div>Código $code: $count (" . round(($count / $config['requests']) * 100, 1) . "%)</div>";
    }, array_keys($stats['codes']), $stats['codes']);
    
    $codeDistributionString = implode('', $codeDistribution);

    $finalResults = <<<FINAL
    <div class="panel">
        <h2>📊 Resultados Finales</h2>
        <div class="stats">
            <div class="panel">
                <h3>General</h3>
                <div>🔢 Total de Solicitudes: {$config['requests']}</div>
                <div>✅ Tasa de Éxito: $successRate%</div>
                <div>⏱ Tiempo Promedio: {$avgTime}ms</div>
                <div>⏱ Percentil 50: {$p50}ms</div>
                <div>⏱ Percentil 95: {$p95}ms</div>
            </div>
            
            <div class="panel">
                <h3>Códigos HTTP</h3>
                {$codeDistributionString}
            </div>
        </div>

        <div style="margin-top:20px;">
            <a href="test_report.php?test_id=$testId" class="btn" style="background:#28a745;margin-right:10px;">Ver Reporte Detallado</a>
            <a href="test_load.php" class="btn">Nueva Prueba</a>
        </div>
    </div>
FINAL;

    echo $finalResults;
}