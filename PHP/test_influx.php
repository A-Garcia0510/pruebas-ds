<?php
// test_influx.php
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

// Habilitar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba de conexión a InfluxDB</h1>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";

try {
    echo "<p>Iniciando creación del cliente...</p>";
    $influxClient = MetricsFactory::createInfluxDBClient();
    echo "<p>Cliente creado, verificando conexión...</p>";
    
    if ($influxClient->isConnected()) {
        echo "<h2 style='color: green;'>¡Conexión exitosa a InfluxDB!</h2>";
        
        // Intenta escribir un dato de prueba
        echo "<p>Intentando escribir datos de prueba...</p>";
        $result = $influxClient->writeData(
            'test_measurement',
            ['value' => 1, 'test' => true],
            ['source' => 'test_script']
        );
        
        if ($result) {
            echo "<p style='color: green;'>✓ Se ha escrito un dato de prueba correctamente.</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Conexión establecida pero error al escribir datos.</p>";
        }
    } else {
        echo "<h2 style='color: red;'>❌ No se pudo conectar a InfluxDB.</h2>";
        echo "<p>Verifica que InfluxDB esté funcionando en la URL configurada y que las credenciales sean correctas.</p>";
        
        // Mostrar información de la configuración para depuración (quita esto en producción)
        echo "<h3>Información de configuración:</h3>";
        echo "<ul>";
        echo "<li>URL: " . MetricsFactory::getConfiguredUrl() . "</li>";
        echo "<li>Usuario: " . MetricsFactory::getConfiguredUser() . "</li>";
        echo "<li>Base de datos: " . MetricsFactory::getConfiguredDB() . "</li>";
        echo "</ul>";
        echo "<p>Nota: Las contraseñas no se muestran por seguridad</p>";
    }
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Traza del error:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}