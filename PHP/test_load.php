<?php
// PHP/test_load.php - Versión optimizada sin monitoreo de servidor
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

    echo str_replace(['{%form%', '{%results%}'], ['', $resultsContent], $htmlTemplate);
    flush();

    // Función optimizada para requests
    $makeRequest = function($url, $testId, $requestId) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$url?loadtest=$testId&request=$requestId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        $start = microtime(true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'time' => round((microtime(true) - $start) * 1000, 2),
            'success' => $httpCode >= 200 && $httpCode < 300,
            'code' => $httpCode
        ];
    };

    // Ejecución de la prueba
    $stats = [
        'total' => 0,
        'success' => 0,
        'times' => [],
        'codes' => []
    ];

    for ($i = 0; $i < $config['requests']; $i += $config['concurrency']) {
        $batchSize = min($config['concurrency'], $config['requests'] - $i);
        $batch = [];
        
        // Ejecutar batch
        for ($j = 0; $j < $batchSize; $j++) {
            $batch[] = $makeRequest($config['url'], $testId, $i + $j + 1);
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
        $rps = round(count($stats['times']) / (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 1);
        
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

    // Resultados finales
    $successRate = round(($stats['success'] / $config['requests']) * 100, 1);
    $codeDistribution = array_map(function($code, $count) use ($config) {
        return "<div>Código $code: $count (" . round(($count / $config['requests']) * 100, 1) . "%)</div>";
    }, array_keys($stats['codes']), $stats['codes']);
    
    $codeDistributionString = implode('', $codeDistribution);
    $avgTime = round(array_sum($stats['times']) / count($stats['times']));

    $finalResults = <<<FINAL
    <div class="panel">
        <h2>📊 Resultados Finales</h2>
        <div class="stats">
            <div class="panel">
                <h3>General</h3>
                <div>🔢 Total de Solicitudes: {$config['requests']}</div>
                <div>✅ Tasa de Éxito: $successRate%</div>
                <div>⏱ Tiempo Promedio: {$avgTime}ms</div>
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
        
        <div style="margin-top:20px;">
            <a href="test_load.php" class="btn">Nueva Prueba</a>
        </div>
    </div>
FINAL;

    echo $finalResults;
}