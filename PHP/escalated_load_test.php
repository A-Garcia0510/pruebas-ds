<?php
// PHP/test_load.php - Versión optimizada para pruebas de límites
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

// Configuración de ejecución para pruebas intensivas
set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M');

// Control del formulario
$runTest = isset($_POST['submit']);
$formSubmitted = isset($_POST['submitted']) && $_POST['submitted'] === 'true';

// Parámetros de la prueba con sanitización y límites ampliados
$config = [
    'requests' => filter_var(
        $formSubmitted 
            ? $_POST['requests'] 
            : (isset($_GET['requests']) ? $_GET['requests'] : 500), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 50000, 'default' => 500]]
    ),
    'concurrency' => filter_var(
        $formSubmitted 
            ? $_POST['concurrency'] 
            : (isset($_GET['concurrency']) ? $_GET['concurrency'] : 25), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 500, 'default' => 25]]
    ),
    'interval' => filter_var(
        $formSubmitted 
            ? $_POST['interval'] 
            : (isset($_GET['interval']) ? $_GET['interval'] : 50), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 0, 'max_range' => 2000, 'default' => 50]]
    ),
    'url' => filter_var(
        $formSubmitted 
            ? $_POST['url'] 
            : (isset($_GET['url']) ? $_GET['url'] : 'http://localhost/pruebas-ds/index.php'), 
        FILTER_VALIDATE_URL
    ),
    'timeout' => filter_var(
        $formSubmitted 
            ? $_POST['timeout'] 
            : (isset($_GET['timeout']) ? $_GET['timeout'] : 20), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 60, 'default' => 20]]
    ),
    'test_duration' => filter_var(
        $formSubmitted 
            ? $_POST['test_duration'] 
            : (isset($_GET['test_duration']) ? $_GET['test_duration'] : 0), 
        FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 0, 'max_range' => 3600, 'default' => 0]]
    ),
    'test_mode' => filter_var(
        $formSubmitted 
            ? $_POST['test_mode'] 
            : (isset($_GET['test_mode']) ? $_GET['test_mode'] : 'standard'), 
        FILTER_SANITIZE_FULL_SPECIAL_CHARS
    )
];

$testId = uniqid('loadtest_');
$startTime = microtime(true);

// Plantilla HTML mejorada
$htmlTemplate = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Carga Avanzada</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .panel { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .progress-bar { height: 25px; background: #28a745; transition: width 0.3s ease; border-radius: 4px; color: white; font-weight: bold; line-height: 25px; text-align: center; }
        .progress-container { background: #e9ecef; border-radius: 4px; }
        .form-group { margin-bottom: 15px; }
        input[type="number"], input[type="url"], select { width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .btn { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-warning { background: #ffc107; }
        .btn-warning:hover { background: #e0a800; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .tabs { display: flex; margin-bottom: 15px; }
        .tab { padding: 10px 15px; cursor: pointer; border: 1px solid #dee2e6; background: #f8f9fa; border-radius: 5px 5px 0 0; }
        .tab.active { background: #007bff; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .chart-container { height: 300px; margin: 20px 0; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-warning { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #dee2e6; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; }
        .response-time-critical { background-color: #f8d7da; }
        .response-time-warning { background-color: #fff3cd; }
        .response-time-good { background-color: #d4edda; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <h1>🚀 Prueba de Carga Avanzada</h1>
    {%form%}
    {%results%}
    
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector('[data-tab="' + tabId + '"]').classList.add('active');
        }
    </script>
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
                <label><strong>URL Objetivo:</strong></label>
                <input type="url" name="url" value="{$config['url']}" required>
            </div>

            <div class="tabs">
                <div class="tab active" data-tab="basic-config" onclick="switchTab('basic-config')">Configuración Básica</div>
                <div class="tab" data-tab="advanced-config" onclick="switchTab('advanced-config')">Configuración Avanzada</div>
            </div>
            
            <div id="basic-config" class="tab-content active">
                <div class="form-group">
                    <label><strong>Solicitudes Totales:</strong></label>
                    <input type="number" name="requests" value="{$config['requests']}" min="1" max="50000" required>
                    <small>Aumentado a máximo 50,000 para pruebas de estrés intensivas</small>
                </div>

                <div class="form-group">
                    <label><strong>Concurrencia:</strong></label>
                    <input type="number" name="concurrency" value="{$config['concurrency']}" min="1" max="500" required>
                    <small>Aumentado a máximo 500 para simular cargas extremas</small>
                </div>
                
                <div class="form-group">
                    <label><strong>Intervalo (ms):</strong></label>
                    <input type="number" name="interval" value="{$config['interval']}" min="0" max="2000" required>
                    <small>Reducido a mínimo 0ms para pruebas intensivas sin espera</small>
                </div>
            </div>
            
            <div id="advanced-config" class="tab-content">
                <div class="form-group">
                    <label><strong>Modo de Prueba:</strong></label>
                    <select name="test_mode">
                        <option value="standard" selected>Estándar (Peticiones fijas)</option>
                        <option value="ramp-up">Rampa (Incremento gradual)</option>
                        <option value="spike">Picos (Variaciones súbitas)</option>
                        <option value="duration">Por Duración (En vez de peticiones)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><strong>Tiempo Máximo de Prueba (segundos):</strong></label>
                    <input type="number" name="test_duration" value="{$config['test_duration']}" min="0" max="3600">
                    <small>0 = Sin límite, máximo 1 hora. Para modo "Por Duración"</small>
                </div>
                
                <div class="form-group">
                    <label><strong>Timeout por Petición (segundos):</strong></label>
                    <input type="number" name="timeout" value="{$config['timeout']}" min="1" max="60">
                </div>
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
        <div><strong>Modo:</strong> {$config['test_mode']}</div>
        
        <div style="margin:15px 0;">
            <div class="progress-container">
                <div class="progress-bar" style="width:0%" id="progressBar">0%</div>
            </div>
        </div>
        
        <div class="tabs">
            <div class="tab active" data-tab="live-summary" onclick="switchTab('live-summary')">Resumen</div>
            <div class="tab" data-tab="live-charts" onclick="switchTab('live-charts')">Gráficos</div>
            <div class="tab" data-tab="live-errors" onclick="switchTab('live-errors')">Errores</div>
        </div>
        
        <div id="live-summary" class="tab-content active">
            <div id="liveStats" class="stats"></div>
        </div>
        
        <div id="live-charts" class="tab-content">
            <div class="chart-container">
                <canvas id="responseTimeChart"></canvas>
            </div>
            <div class="chart-container">
                <canvas id="throughputChart"></canvas>
            </div>
        </div>
        
        <div id="live-errors" class="tab-content">
            <div id="errorStats"></div>
        </div>
        
        <button id="stopButton" class="btn btn-warning" onclick="stopTest()" style="display:none;">Detener Prueba</button>
    </div>
    
    <script>
        // Inicialización de gráficos
        let timeData = [];
        let responseData = [];
        let throughputData = [];
        let labels = [];
        
        const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
        const responseTimeChart = new Chart(responseTimeCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tiempo de Respuesta (ms)',
                    data: responseData,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: false
            }
        });
        
        const throughputCtx = document.getElementById('throughputChart').getContext('2d');
        const throughputChart = new Chart(throughputCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Peticiones/Segundo',
                    data: throughputData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: false
            }
        });
        
        function stopTest() {
            fetch('?action=stop&testId=' + '$testId');
            document.getElementById('stopButton').disabled = true;
            document.getElementById('stopButton').innerHTML = 'Deteniendo...';
        }
        
        // Mostrar botón de detener después de iniciar
        setTimeout(() => {
            document.getElementById('stopButton').style.display = 'block';
        }, 2000);
    </script>
RESULTS;

    echo str_replace(['{%form%', '{%results%}'], ['', $resultsContent], $htmlTemplate);
    flush();

    // Variables para estadísticas
    $stats = [
        'total' => 0,
        'success' => 0,
        'times' => [],
        'codes' => [],
        'errors' => [],
        'throughput' => [],
        'percentiles' => [
            'p50' => 0,
            'p90' => 0,
            'p95' => 0,
            'p99' => 0
        ],
        'min' => PHP_INT_MAX,
        'max' => 0,
        'time_ranges' => [
            '0-100ms' => 0,
            '100-300ms' => 0,
            '300-500ms' => 0,
            '500-1000ms' => 0,
            '1000-2000ms' => 0,
            '2000ms+' => 0
        ]
    ];

    $testRunning = true;
    $startTime = microtime(true);
    $lastUpdateTime = $startTime;
    $updateInterval = 1.0; // Actualizar UI cada segundo
    $shouldStop = function() use ($config, $startTime) {
        // Verificar si debemos detener según duración
        if ($config['test_duration'] > 0 && (microtime(true) - $startTime) > $config['test_duration']) {
            return true;
        }
        
        // Verificar petición de detención vía GET
        if (isset($_GET['action']) && $_GET['action'] === 'stop' && isset($_GET['testId']) && $_GET['testId'] === $testId) {
            return true;
        }
        
        return false;
    };

    // Función optimizada para requests con métricas detalladas
    $makeRequest = function($url, $testId, $requestId, $timeout) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$url?loadtest=$testId&request=$requestId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HEADER => true
        ]);
        
        $start = microtime(true);
        $response = curl_exec($ch);
        $time = round((microtime(true) - $start) * 1000, 2);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $error = curl_error($ch);
        $headers = substr($response, 0, $headerSize);
        // $body = substr($response, $headerSize); // Removed as it's unused
        
        curl_close($ch);
        
        // Extraer información de respuesta
        $serverInfo = [];
        if (preg_match('/X-Processing-Time: ([\d\.]+)ms/i', $headers, $matches)) {
            $serverInfo['processing_time'] = floatval($matches[1]);
        }
        
        if (preg_match('/X-Memory-Usage: ([\d\.]+)MB/i', $headers, $matches)) {
            $serverInfo['memory_usage'] = floatval($matches[1]);
        }
        
        return [
            'time' => $time,
            'success' => $httpCode >= 200 && $httpCode < 400,
            'code' => $httpCode,
            'error' => $error,
            'server_info' => $serverInfo,
        ];
    };

    // Determinar total de solicitudes según el modo
    $totalRequests = $config['requests'];
    $actualConcurrency = $config['concurrency'];
    
    // Función para actualizar la UI con datos en vivo
    $updateUI = function($stats, $progress, $totalRequests) use ($startTime, &$lastUpdateTime) {
        $currentTime = microtime(true);
        
        if (($currentTime - $lastUpdateTime) < 0.5) {
            return; // Limitar actualizaciones para no sobrecargar el navegador
        }
        
        $lastUpdateTime = $currentTime;
        $elapsed = $currentTime - $startTime;
        $avgTime = count($stats['times']) > 0 
            ? round(array_sum($stats['times']) / count($stats['times'])) 
            : 0;
        
        // Calcular percentiles
        if (count($stats['times']) > 0) {
            sort($stats['times']);
            $stats['percentiles']['p50'] = $stats['times'][floor(count($stats['times']) * 0.5)];
            $stats['percentiles']['p90'] = $stats['times'][floor(count($stats['times']) * 0.9)];
            $stats['percentiles']['p95'] = $stats['times'][floor(count($stats['times']) * 0.95)];
            $stats['percentiles']['p99'] = $stats['times'][floor(count($stats['times']) * 0.99)];
        }
        
        // Calcular RPS actual
        $rps = round($stats['total'] / max(1, $elapsed), 1);
        
        // Preparar datos para gráficos
        $responseDataPoint = $avgTime;
        $throughputDataPoint = $rps;
        
        // Preparar lista de errores
        $errorHtml = '';
        if (!empty($stats['errors'])) {
            $errorHtml = '<div class="alert alert-warning">Se encontraron errores:</div><table><thead><tr><th>Error</th><th>Ocurrencias</th></tr></thead><tbody>';
            foreach ($stats['errors'] as $error => $count) {
                $errorHtml .= "<tr><td>$error</td><td>$count</td></tr>";
            }
            $errorHtml .= '</tbody></table>';
        } else {
            $errorHtml = '<div class="alert alert-warning">No se han encontrado errores hasta el momento</div>';
        }
        
        // Actualizar vista
        $liveStats = <<<JS
        <script>
            document.getElementById('progressBar').style.width = '$progress%';
            document.getElementById('progressBar').innerHTML = '$progress% ($stats[total]/$totalRequests)';
            
            document.getElementById('liveStats').innerHTML = `
                <div class="panel">
                    <h3>Resumen</h3>
                    <div>⏱ Tiempo de Ejecución: <script>document.write(Math.round(<?php echo $elapsed; ?>) + 's');</script></div>
                    <div>💨 Velocidad: $rps req/s</div>
                    <div>✅ Éxitos: <?php echo $stats['success']; ?>/<?php echo $stats['total']; ?> (<script>document.write(Math.round((<?php echo $stats['success']; ?>/<?php echo $stats['total']; ?>)*100) + '%');</script>)</div>
                </div>
                
                <div class="panel">
                    <h3>Tiempos de Respuesta</h3>
                    <div>⏱ Promedio: $avgTime ms</div>
                    <div>⬇️ Mínimo: $stats[min] ms</div>
                    <div>⬆️ Máximo: $stats[max] ms</div>
                    <div>📊 Percentil 50: $stats[percentiles][p50] ms</div>
                    <div>📊 Percentil 90: $stats[percentiles][p90] ms</div>
                    <div>📊 Percentil 95: $stats[percentiles][p95] ms</div>
                    <div>📊 Percentil 99: $stats[percentiles][p99] ms</div>
                </div>
            `;
            
            document.getElementById('errorStats').innerHTML = `$errorHtml`;
            
            // Actualizar gráficos
            labels.push(Math.round(<?php echo $elapsed; ?>) + 's');
            responseData.push($responseDataPoint);
            throughputData.push($throughputDataPoint);
            
            // Limitar puntos de datos para rendimiento
            if (labels.length > 30) {
                labels.shift();
                responseData.shift();
                throughputData.shift();
            }
            
            responseTimeChart.update();
            throughputChart.update();
        </script>
JS;
        
        echo $liveStats;
        flush();
    };

    // Ejecución de la prueba según el modo seleccionado
    switch ($config['test_mode']) {
        case 'ramp-up':
            // Modo rampa - incremento gradual de concurrencia
            $steps = 10;
            $baseRequests = ceil($totalRequests / $steps);
            $maxConcurrency = $config['concurrency'];
            
            for ($step = 1; $step <= $steps && !$shouldStop(); $step++) {
                $stepConcurrency = max(1, ceil(($maxConcurrency / $steps) * $step));
                $stepRequests = $baseRequests;
                
                for ($i = 0; $i < $stepRequests && !$shouldStop(); $i += $stepConcurrency) {
                    $batchSize = min($stepConcurrency, $stepRequests - $i);
                    $batch = [];
                    
                    // Ejecutar batch
                    for ($j = 0; $j < $batchSize; $j++) {
                        $batch[] = $makeRequest($config['url'], $testId, $stats['total'] + 1, $config['timeout']);
                    }
                    
                    // Procesar resultados
                    foreach ($batch as $result) {
                        $stats['total']++;
                        if ($result['success']) $stats['success']++;
                        $stats['times'][] = $result['time'];
                        $stats['min'] = min($stats['min'], $result['time']);
                        $stats['max'] = max($stats['max'], $result['time']);
                        
                        // Categorizar por tiempo
                        if ($result['time'] < 100) $stats['time_ranges']['0-100ms']++;
                        elseif ($result['time'] < 300) $stats['time_ranges']['100-300ms']++;
                        elseif ($result['time'] < 500) $stats['time_ranges']['300-500ms']++;
                        elseif ($result['time'] < 1000) $stats['time_ranges']['500-1000ms']++;
                        elseif ($result['time'] < 2000) $stats['time_ranges']['1000-2000ms']++;
                        else $stats['time_ranges']['2000ms+']++;
                        
                        @$stats['codes'][$result['code']]++;
                        
                        if (!$result['success'] && !empty($result['error'])) {
                            @$stats['errors'][$result['error']]++;
                        }
                    }
                    
                    // Actualizar interfaz
                    $progress = round($stats['total'] / $totalRequests * 100);
                    $updateUI($stats, $progress, $totalRequests);
                    
                    // Intervalo entre batches
                    if ($config['interval'] > 0) {
                        usleep($config['interval'] * 1000);
                    }
                }
                
                // Mensaje de cambio de fase
                echo "<script>console.log('Fase $step completada: $stepConcurrency usuarios concurrentes');</script>";
                flush();
            }
            break;
            
        case 'spike':
            // Modo picos - alternar entre bajo y alto volumen
            $cycles = 5;
            $requestsPerCycle = ceil($totalRequests / $cycles);
            
            for ($cycle = 1; $cycle <= $cycles && !$shouldStop(); $cycle++) {
                // Fase de baja concurrencia (25%)
                $lowConcurrency = max(1, floor($config['concurrency'] * 0.25));
                $highConcurrency = $config['concurrency'];
                
                // Alternar entre bajo y alto
                $cycleConcurrency = ($cycle % 2 == 1) ? $lowConcurrency : $highConcurrency;
                
                for ($i = 0; $i < $requestsPerCycle && !$shouldStop(); $i += $cycleConcurrency) {
                    $batchSize = min($cycleConcurrency, $requestsPerCycle - $i);
                    $batch = [];
                    
                    // Ejecutar batch
                    for ($j = 0; $j < $batchSize; $j++) {
                        $batch[] = $makeRequest($config['url'], $testId, $stats['total'] + 1, $config['timeout']);
                    }
                    
                    // Procesar resultados
                    foreach ($batch as $result) {
                        $stats['total']++;
                        if ($result['success']) $stats['success']++;
                        $stats['times'][] = $result['time'];
                        $stats['min'] = min($stats['min'], $result['time']);
                        $stats['max'] = max($stats['max'], $result['time']);
                        
                        // Categorizar por tiempo
                        if ($result['time'] < 100) $stats['time_ranges']['0-100ms']++;
                        elseif ($result['time'] < 300) $stats['time_ranges']['100-300ms']++;
                        elseif ($result['time'] < 500) $stats['time_ranges']['300-500ms']++;
                        elseif ($result['time'] < 1000) $stats['time_ranges']['500-1000ms']++;
                        elseif ($result['time'] < 2000) $stats['time_ranges']['1000-2000ms']++;
                        else $stats['time_ranges']['2000ms+']++;
                        
                        @$stats['codes'][$result['code']]++;
                        
                        if (!$result['success'] && !empty($result['error'])) {
                            @$stats['errors'][$result['error']]++;
                        }
                    }
                    
                    // Actualizar interfaz
                    $progress = round($stats['total'] / $totalRequests * 100);
                    $updateUI($stats, $progress, $totalRequests);
                    
                    // Intervalo entre batches
                    if ($config['interval'] > 0) {
                        usleep($config['interval'] * 1000);
                    }
                }
                
                // Mensaje de cambio de ciclo
                $phaseType = ($cycle % 2 == 1) ? "baja" : "alta";
                echo "<script>console.log('Ciclo $cycle completado: carga $phaseType con $cycleConcurrency usuarios');</script>";
                flush();
            }
            break;
            
        case 'duration':
            // Modo duración - ejecutar hasta tiempo límite
            if ($config['test_duration'] <= 0) {
                $config['test_duration'] = 60; // Default 1 minuto
            }
            
            $endTime = $startTime + $config['test_duration'];
            $requestCount = 0;
            
            while (microtime(true) < $endTime && !$shouldStop()) {
                $batch = [];
                
                // Ejecutar batch
                for ($j = 0; $j < $config['concurrency']; $j++) {
                    $batch[] = $makeRequest($config['url'], $testId, $stats['total'] + 1, $config['timeout']);
                }
                
                // Procesar resultados
                foreach ($batch as $result) {
                    $stats['total']++;
                    if ($result['success']) $stats['success']++;
                    $stats['times'][] = $result['time'];
                    $stats['min'] = min($stats['min'], $result['time']);
                    $stats['max'] = max($stats['max'], $result['time']);
                    
                    // Categorizar por tiempo
                    if ($result['time'] < 100) $stats['time_ranges']['0-100ms']++;
                    elseif ($result['time'] < 300) $stats['time_ranges']['100-300ms']++;
                    elseif ($result['time'] < 500) $stats['time_ranges']['300-500ms']++;
                    elseif ($result['time'] < 1000) $stats['time_ranges']['500-1000ms']++;
                    elseif ($result['time'] < 2000) $stats['time_ranges']['1000-2000ms']++;
                    else $stats['time_ranges']['2000ms+']++;
                    
                    @$stats['codes'][$result['code']]++;
                    
                    if (!$result['success'] && !empty($result['error'])) {
                        @$stats['errors'][$result['error']]++;
                    }
                }
                
                // Actualizar interfaz con progreso basado en tiempo
                $elapsedTime = microtime(true) - $startTime;
                $timeProgress = min(100, round(($elapsedTime / $config['test_duration']) * 100));
                $updateUI($stats, $timeProgress, "Por tiempo: " . round($elapsedTime) . "s / {$config['test_duration']}s");
                
                // Intervalo entre batches
                if ($config['interval'] > 0) {
                    usleep($config['interval'] * 1000);
                }
                
                $requestCount += $config['concurrency'];
            }
            
            // Actualizar totalRequests para mostrar estadísticas finales correctas
            $totalRequests = $stats['total'];
            break;
            
        default:
            // Modo estándar - ejecutar número fijo de solicitudes
            for ($i = 0; $i < $totalRequests && !$shouldStop(); $i += $config['concurrency']) {
                $batchSize = min($config['concurrency'], $totalRequests - $i);
                $batch = [];
                
                // Ejecutar batch
                for ($j = 0; $j < $batchSize; $j++) {
                    $batch[] = $makeRequest($config['url'], $testId, $stats['total'] + 1, $config['timeout']);
                }
                
                // Procesar resultados
                foreach ($batch as $result) {
                    $stats['total']++;
                    if ($result['success']) $stats['success']++;
                    $stats['times'][] = $result['time'];
                    $stats['min'] = min($stats['min'], $result['time']);
                    $stats['max'] = max($stats['max'], $result['time']);
                    
                    // Categorizar por tiempo
                    if ($result['time'] < 100) $stats['time_ranges']['0-100ms']++;
                    elseif ($result['time'] < 300) $stats['time_ranges']['100-300ms']++;
                    elseif ($result['time'] < 500) $stats['time_ranges']['300-500ms']++;
                    elseif ($result['time'] < 1000) $stats['time_ranges']['500-1000ms']++;
                    elseif ($result['time'] < 2000) $stats['time_ranges']['1000-2000ms']++;
                    else $stats['time_ranges']['2000ms+']++;
                    
                    @$stats['codes'][$result['code']]++;
                    
                    if (!$result['success'] && !empty($result['error'])) {
                        @$stats['errors'][$result['error']]++;
                    }
                    
                    // Enviar datos a InfluxDB si está disponible
                    try {
                        $measurement = [
                            'name' => 'load_test',
                            'tags' => [
                                'test_id' => $testId,
                                'url' => $config['url'],
                                'success' => $result['success'] ? 'true' : 'false',
                                'code' => $result['code']
                            ],
                            'fields' => [
                                'response_time' => $result['time'],
                                'memory_usage' => $result['server_info']['memory_usage'] ?? 0,
                                'processing_time' => $result['server_info']['processing_time'] ?? 0
                            ],
                            'timestamp' => time()
                        ];
                        
                        if ($influxClient) {
                            $influxClient->writePoints([$measurement]);
                        }
                    } catch (Exception $e) {
                        // Ignorar errores de registro, no interrumpir la prueba
                    }
                }
                
                // Actualizar interfaz
                $progress = round($stats['total'] / $totalRequests * 100);
                $updateUI($stats, $progress, $totalRequests);
                
                // Intervalo entre batches
                if ($config['interval'] > 0) {
                    usleep($config['interval'] * 1000);
                }
            }
            break;
    }
    $successPercentage = $stats['total'] > 0 ? round(($stats['success'] / $stats['total']) * 100, 1) : 0;
    $failedRequests = $stats['total'] - $stats['success'];
    $failedPercentage = $stats['total'] > 0 ? round(($failedRequests / $stats['total']) * 100, 1) : 0;
    $requestsPerSecond = $stats['total'] > 0 ? round($stats['total'] / $totalTime, 1) : 0;
    $averageResponseTime = count($stats['times']) > 0 ? round(array_sum($stats['times']) / count($stats['times']), 1) : 0;
    $minResponseTime = $stats['min'] != PHP_INT_MAX ? $stats['min'] : 0;
    
    // Tiempo total de ejecución
    $totalTime = round(microtime(true) - $startTime, 2);
    
    // Preparar informe final
    $finalReport = <<<HTML
    <div class="panel">
        <h2>🏁 Prueba Completada</h2>
        <p><strong>Tiempo Total:</strong> $totalTime segundos</p>
        
        <div class="tabs">
            <div class="tab active" data-tab="summary" onclick="switchTab('summary')">Resumen</div>
            <div class="tab" data-tab="detailed" onclick="switchTab('detailed')">Detallado</div>
            <div class="tab" data-tab="response-distribution" onclick="switchTab('response-distribution')">Distribución</div>
        </div>
        
        <div id="summary" class="tab-content active">
            <h3>Resumen General</h3>
            <div class="stats">
                <div class="panel">
                    <h4>Solicitudes</h4>
                    <p>Total: {$stats['total']}</p>
                    <p>Exitosas: {$stats['success']} ({$successPercentage}%)</p>
                    <p>Fallidas: {$failedRequests} ({$failedPercentage}%)</p>
                </div>
                
                <div class="panel">
                    <h4>Rendimiento</h4>
                    <<p>Solicitudes por segundo: {$requestsPerSecond} req/s</p>
                    <p>Tiempo promedio de respuesta: {$averageResponseTime} ms</p>
                </div>
                
                <div class="panel">
                    <h4>Tiempos</h4>
                    <p>Mínimo: {$minResponseTime} ms</p>
                    <p>Máximo: {$stats['max']} ms</p>
                    <p>Percentil 90: {$stats['percentiles']['p90']} ms</p>
                    <p>Percentil 95: {$stats['percentiles']['p95']} ms</p>
                </div>
            </div>
        </div>
        
        <div id="detailed" class="tab-content">
            <h3>Detalles de la Prueba</h3>
            
            <h4>Códigos de Estado HTTP</h4>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cantidad</th>
                        <th>Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
HTML;

    // Agregar códigos de estado
    foreach ($stats['codes'] as $code => $count) {
        $percent = round(($count / $stats['total']) * 100, 1);
        $finalReport .= "<tr><td>$code</td><td>$count</td><td>$percent%</td></tr>";
    }

    $finalReport .= <<<HTML
                </tbody>
            </table>
            
            <h4>Errores Encontrados</h4>
HTML;

    // Agregar errores si existen
    if (!empty($stats['errors'])) {
        $finalReport .= '<table><thead><tr><th>Error</th><th>Ocurrencias</th></tr></thead><tbody>';
        foreach ($stats['errors'] as $error => $count) {
            $finalReport .= "<tr><td>$error</td><td>$count</td></tr>";
        }
        $finalReport .= '</tbody></table>';
    } else {
        $finalReport .= '<p>No se encontraron errores</p>';
    }

    $finalReport .= <<<HTML
        </div>
        
        <div id="response-distribution" class="tab-content">
            <h3>Distribución de Tiempos de Respuesta</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Rango</th>
                        <th>Cantidad</th>
                        <th>Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
HTML;

    // Agregar distribución de tiempos
    foreach ($stats['time_ranges'] as $range => $count) {
        $percent = $stats['total'] > 0 ? round(($count / $stats['total']) * 100, 1) : 0;
        $finalReport .= "<tr><td>$range</td><td>$count</td><td>$percent%</td></tr>";
    }

    $finalReport .= <<<HTML
                </tbody>
            </table>
            
            <div class="chart-container">
                <canvas id="timeDistributionChart"></canvas>
            </div>
            <script>
                const timeDistributionCtx = document.getElementById('timeDistributionChart').getContext('2d');
                const timeDistributionChart = new Chart(timeDistributionCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            '0-100ms', '100-300ms', '300-500ms', '500-1000ms', '1000-2000ms', '2000ms+'
                        ],
                        datasets: [{
                            label: 'Distribución de tiempos de respuesta',
                            data: [
                                {$stats['time_ranges']['0-100ms']},
                                {$stats['time_ranges']['100-300ms']},
                                {$stats['time_ranges']['300-500ms']},
                                {$stats['time_ranges']['500-1000ms']},
                                {$stats['time_ranges']['1000-2000ms']},
                                {$stats['time_ranges']['2000ms+']}
                            ],
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.5)',
                                'rgba(54, 162, 235, 0.5)',
                                'rgba(255, 206, 86, 0.5)',
                                'rgba(255, 159, 64, 0.5)',
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(153, 102, 255, 0.5)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(153, 102, 255, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        </div>
        
        <div style="margin-top:20px;">
            <a href="test_load.php" class="btn">Nueva Prueba</a>
        </div>
    </div>
HTML;

    echo "<script>document.getElementById('stopButton').style.display = 'none';</script>";
    echo $finalReport;
    echo "<script>switchTab('summary');</script>";
}
// Removed redundant closing tag