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
    <h1>Informe de Prueba de Carga</h