<?php
// test_influx.php
require_once __DIR__ . '/PHP/autoload.php';

use Metrics\MetricsFactory;

try {
    $influxClient = MetricsFactory::createInfluxDBClient();
    
    if ($influxClient->isConnected()) {
        echo "<h2 style='color: green;'>¡Conexión exitosa a InfluxDB!</h2>";
        
        // Intenta escribir un dato de prueba
        $result = $influxClient->writeData(
            'test_measurement',
            ['value' => 1, 'test' => true],
            ['source' => 'test_script']
        );
        
        if ($result) {
            echo "<p>Se ha escrito un dato de prueba correctamente.</p>";
        } else {
            echo "<p style='color: orange;'>Conexión establecida pero error al escribir datos.</p>";
        }
    } else {
        echo "<h2 style='color: red;'>No se pudo conectar a InfluxDB.</h2>";
    }
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}