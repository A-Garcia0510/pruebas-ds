<?php
// PHP/test_report.php
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

// Obtener el ID de prueba desde la URL
$testId = isset($_GET['test_id']) ? $_GET['test_id'] : null;

if (!$testId) {
    die("Error: Se requiere un ID de prueba válido");
}

// Preparar el cliente InfluxDB
$influxClient = MetricsFactory::createInfluxDBClient();
if (!$influxClient->isConnected()) {
    die("Error: No se pudo conectar a InfluxDB para recuperar los datos de la prueba.");
}

// Obtener los datos generales de la prueba
$testQuery = <<<FLUX
from(bucket: "{$influxClient->getBucket()}")
  |> range(start: -7d)
  |> filter(fn: (r) => r._measurement == "load_test")
  |> filter(fn: (r) => r.test_id == "{$testId}")
FLUX;

$testData = $influxClient->query($testQuery);

// Obtener las métricas de cada solicitud
$requestsQuery = <<<FLUX
from(bucket: "{$influxClient->getBucket()}")
  |> range(start: -7d)
  |> filter(fn: (r) => r._measurement == "request_metrics")
  |> filter(fn: (r) => r.test_id == "{$testId}")
FLUX;

$requestsData = $influxClient->query($requestsQuery);

// Verificar si tenemos datos
if (empty($testData) || empty($requestsData)) {
    die("Error: No se encontraron datos para el ID de prueba especificado.");
}

// Extraer información relevante
$testInfo = [];
$requestsInfo = [];

foreach ($testData as $point) {
    if (!isset($point['_field']) || !isset($point['_value'])) continue;
    $testInfo[$point['_field']] = $point['_value'];
    // Capturar metadata
    if (isset($point['target_url']) && !isset($testInfo['target_url'])) {
        $testInfo['target_url'] = $point['target_url'];
    }
    if (isset($point['test_type']) && $point['test_type'] === 'start' && isset($point['_time'])) {
        $testInfo['start_time'] = $point['_time'];
    }
}

foreach ($requestsData as $point) {
    if (!isset($point['_field']) || !isset($point['_value']) || !isset($point['request_id'])) continue;
    
    $requestId = $point['request_id'];
    if (!isset($requestsInfo[$requestId])) {
        $requestsInfo[$requestId] = [];
    }
    
    $requestsInfo[$requestId][$point['_field']] = $point['_value'];
    if (isset($point['_time'])) {
        $requestsInfo[$requestId]['time'] = $point['_time'];
    }
}

// Preparar datos para gráficos
$responseTimesData = [];
$successRateData = [];

foreach ($requestsInfo as $id => $request) {
    if (isset($request['response_time_ms'])) {
        $responseTimesData[] = [
            'id' => $id,
            'time' => $request['response_time_ms']
        ];
    }
    
    if (isset($request['success'])) {
        $successRateData[] = [
            'id' => $id,
            'success' => $request['success']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Prueba de Carga - <?php echo htmlspecialchars($testId); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .metric-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: #3498db;
        }
        .metric-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .success {
            color: green;
        }
        .failure {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Informe de Prueba de Carga</h1>
    
    <div class="card">
        <h2>Información General</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">ID de Prueba</div>
                <div class="metric-value"><?php echo htmlspecialchars($testId); ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">URL Objetivo</div>
                <div class="metric-value" style="font-size: 16px;"><?php echo htmlspecialchars($testInfo['target_url'] ?? 'No disponible'); ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Solicitudes Totales</div>
                <div class="metric-value"><?php echo isset($testInfo['total_requests']) ? number_format($testInfo['total_requests']) : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Solicitudes Exitosas</div>
                <div class="metric-value"><?php echo isset($testInfo['successful_requests']) ? number_format($testInfo['successful_requests']) : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Solicitudes Fallidas</div>
                <div class="metric-value"><?php echo isset($testInfo['failed_requests']) ? number_format($testInfo['failed_requests']) : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Duración Total</div>
                <div class="metric-value"><?php echo isset($testInfo['test_duration_sec']) ? number_format($testInfo['test_duration_sec'], 2) . ' s' : 'N/A'; ?></div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>Métricas de Rendimiento</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Tiempo Promedio de Respuesta</div>
                <div class="metric-value"><?php echo isset($testInfo['avg_response_time_ms']) ? number_format($testInfo['avg_response_time_ms'], 2) . ' ms' : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Percentil 50 (Mediana)</div>
                <div class="metric-value"><?php echo isset($testInfo['p50_response_time_ms']) ? number_format($testInfo['p50_response_time_ms'], 2) . ' ms' : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Percentil 90</div>
                <div class="metric-value"><?php echo isset($testInfo['p90_response_time_ms']) ? number_format($testInfo['p90_response_time_ms'], 2) . ' ms' : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Percentil 95</div>
                <div class="metric-value"><?php echo isset($testInfo['p95_response_time_ms']) ? number_format($testInfo['p95_response_time_ms'], 2) . ' ms' : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Percentil 99</div>
                <div class="metric-value"><?php echo isset($testInfo['p99_response_time_ms']) ? number_format($testInfo['p99_response_time_ms'], 2) . ' ms' : 'N/A'; ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Tasa de Éxito</div>
                <div class="metric-value">
                    <?php 
                    if (isset($testInfo['successful_requests']) && isset($testInfo['total_requests']) && $testInfo['total_requests'] > 0) {
                        echo number_format(($testInfo['successful_requests'] / $testInfo['total_requests']) * 100, 2) . '%';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2>Gráficos de Rendimiento</h2>
        
        <!-- Gráfico de tiempos de respuesta -->
        <h3>Tiempos de Respuesta por Solicitud</h3>
        <div class="chart-container">
            <canvas id="responseTimeChart"></canvas>
        </div>
        
        <!-- Gráfico de distribución de tiempos de respuesta -->
        <h3>Distribución de Tiempos de Respuesta</h3>
        <div class="chart-container">
            <canvas id="responseTimeDistributionChart"></canvas>
        </div>
        
        <!-- Gráfico de tasa de éxito -->
        <h3>Tasa de Éxito</h3>
        <div class="chart-container">
            <canvas id="successRateChart"></canvas>
        </div>
    </div>
    
    <div class="card">
        <h2>Detalles de Solicitudes</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Estado</th>
                        <th>Código HTTP</th>
                        <th>Tiempo de Respuesta (ms)</th>
                        <th>Tamaño de Respuesta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ordenar solicitudes por ID
                    ksort($requestsInfo);
                    
                    foreach ($requestsInfo as $requestId => $requestData): 
                        $status = isset($requestData['success']) && $requestData['success'] ? 'Éxito' : 'Error';
                        $statusClass = isset($requestData['success']) && $requestData['success'] ? 'success' : 'failure';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($requestId); ?></td>
                        <td class="<?php echo $statusClass; ?>"><?php echo $status; ?></td>
                        <td><?php echo isset($requestData['http_code']) ? htmlspecialchars($requestData['http_code']) : 'N/A'; ?></td>
                        <td><?php echo isset($requestData['response_time_ms']) ? number_format($requestData['response_time_ms'], 2) : 'N/A'; ?></td>
                        <td><?php echo isset($requestData['content_length']) ? number_format($requestData['content_length']) . ' bytes' : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    // Preparar datos para los gráficos
    const responseTimesData = <?php echo json_encode($responseTimesData); ?>;
    const successRateData = <?php echo json_encode($successRateData); ?>;
    
    // Crear gráfico de tiempo de respuesta
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseTimeCtx, {
        type: 'line',
        data: {
            labels: responseTimesData.map(item => item.id),
            datasets: [{
                label: 'Tiempo de Respuesta (ms)',
                data: responseTimesData.map(item => item.time),
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Tiempo (ms)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'ID de Solicitud'
                    }
                }
            }
        }
    });
    
    // Crear histograma de distribución de tiempos de respuesta
    const responseTimeDistribution = () => {
        const times = responseTimesData.map(item => item.time);
        const min = Math.min(...times);
        const max = Math.max(...times);
        const range = max - min;
        const binCount = Math.min(20, Math.ceil(Math.sqrt(times.length)));
        const binWidth = range / binCount;
        
        const bins = Array(binCount).fill(0);
        const binLabels = [];
        
        // Establecer etiquetas de bins
        for (let i = 0; i < binCount; i++) {
            const lowerBound = min + (i * binWidth);
            const upperBound = min + ((i + 1) * binWidth);
            binLabels.push(`${lowerBound.toFixed(0)}-${upperBound.toFixed(0)}`);
        }
        
        // Contar frecuencias
        times.forEach(time => {
            if (time === max) {
                bins[binCount - 1]++;
            } else {
                const binIndex = Math.floor((time - min) / binWidth);
                bins[binIndex]++;
            }
        });
        
        return { bins, binLabels };
    };
    
    const distData = responseTimeDistribution();
    const responseTimeDistCtx = document.getElementById('responseTimeDistributionChart').getContext('2d');
    new Chart(responseTimeDistCtx, {
        type: 'bar',
        data: {
            labels: distData.binLabels,
            datasets: [{
                label: 'Frecuencia',
                data: distData.bins,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Solicitudes'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tiempo de Respuesta (ms)'
                    }
                }
            }
        }
    });
    
    // Crear gráfico de tasa de éxito
    const successCount = successRateData.filter(item => item.success).length;
    const failureCount = successRateData.length - successCount;
    const successRate = (successCount / successRateData.length) * 100;
    
    const successRateCtx = document.getElementById('successRateChart').getContext('2d');
    new Chart(successRateCtx, {
        type: 'doughnut',
        data: {
            labels: ['Éxito', 'Error'],
            datasets: [{
                data: [successCount, failureCount],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(255, 99, 132, 0.6)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = (value / successRateData.length * 100).toFixed(2);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>