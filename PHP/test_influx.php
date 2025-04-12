<?php
// test_influx.php
require_once __DIR__ . '/../PHP/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Metrics\MetricsFactory;

// Habilitar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba de conexión a InfluxDB v2</h1>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";

try {
    echo "<p>Iniciando creación del cliente...</p>";
    $influxClient = MetricsFactory::createInfluxDBClient();
    echo "<p>Cliente creado, verificando conexión...</p>";
    
    if ($influxClient->isConnected()) {
        echo "<h2 style='color: green;'>¡Conexión exitosa a InfluxDB v2!</h2>";
        
        // Comprobar si el bucket existe
        echo "<p>Verificando buckets disponibles...</p>";
        
        try {
            $buckets = $influxClient->query('SHOW DATABASES');
            
            if (empty($buckets)) {
                echo "<p style='color: orange;'>No se pudieron obtener los buckets o la lista está vacía.</p>";
                echo "<p>Mensaje: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
                
                // Intentar crear el bucket directamente
                echo "<p>Intentando crear el bucket directamente...</p>";
                $createResult = $influxClient->query('CREATE DATABASE ' . MetricsFactory::getConfiguredBucket());
                if (!empty($createResult) && isset($createResult[0]['success']) && $createResult[0]['success']) {
                    echo "<p style='color: green;'>✓ Bucket creado correctamente.</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ No se pudo confirmar la creación del bucket.</p>";
                }
            } else {
                echo "<p>Buckets disponibles:</p>";
                echo "<ul>";
                $bucketExists = false;
                foreach ($buckets as $bucket) {
                    if (isset($bucket['name'])) {
                        echo "<li>" . htmlspecialchars($bucket['name']) . "</li>";
                        if ($bucket['name'] === MetricsFactory::getConfiguredBucket()) {
                            $bucketExists = true;
                        }
                    }
                }
                echo "</ul>";
                
                if (!$bucketExists) {
                    echo "<p style='color: orange;'>⚠ El bucket '" . htmlspecialchars(MetricsFactory::getConfiguredBucket()) . "' no existe. Esto podría ser la causa del error.</p>";
                    echo "<p>Intentando crear el bucket...</p>";
                    $createResult = $influxClient->query('CREATE DATABASE ' . MetricsFactory::getConfiguredBucket());
                    if (!empty($createResult) && isset($createResult[0]['success']) && $createResult[0]['success']) {
                        echo "<p style='color: green;'>✓ Bucket creado correctamente.</p>";
                    } else {
                        echo "<p style='color: orange;'>⚠ No se pudo confirmar la creación del bucket.</p>";
                    }
                }
            }
        } catch (Exception $dbError) {
            echo "<p style='color: orange;'>Error al consultar buckets: " . htmlspecialchars($dbError->getMessage()) . "</p>";
            echo "<p>Intentando continuar con la operación de escritura...</p>";
        }
        
        // Intenta escribir un dato de prueba
        echo "<p>Intentando escribir datos de prueba...</p>";
        $result = $influxClient->writeData(
            'test_measurement',
            ['value' => 1, 'test' => true],
            ['source' => 'test_script']
        );
        
        if ($result) {
            echo "<p style='color: green;'>✓ Se ha escrito un dato de prueba correctamente.</p>";
            
            // Verificar que el dato se escribió correctamente con Flux
            echo "<p>Verificando que el dato se ha escrito correctamente...</p>";
            $fluxQuery = 'from(bucket: "' . MetricsFactory::getConfiguredBucket() . '")
                |> range(start: -1h)
                |> filter(fn: (r) => r._measurement == "test_measurement")
                |> filter(fn: (r) => r.source == "test_script")
                |> limit(n: 1)';
            $data = $influxClient->query($fluxQuery);
            
            if (!empty($data)) {
                echo "<p style='color: green;'>✓ Dato verificado correctamente:</p>";
                echo "<pre>" . print_r($data[0], true) . "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠ No se pudo verificar el dato.</p>";
                echo "<p>Error: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
                echo "<p>Esto puede deberse a que la consulta es incorrecta o el bucket no contiene los datos aún.</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Conexión establecida pero error al escribir datos.</p>";
            echo "<p>Error: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
            
            // Comprobar permisos del usuario
            echo "<p>Verificando permisos del token...</p>";
            echo "<p>Este error puede ocurrir si el token proporcionado no tiene permisos para escribir en el bucket '" . htmlspecialchars(MetricsFactory::getConfiguredBucket()) . "'.</p>";
        }
    } else {
        echo "<h2 style='color: red;'>❌ No se pudo conectar a InfluxDB v2.</h2>";
        echo "<p>Error: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
        echo "<p>Verifica que InfluxDB esté funcionando en la URL configurada y que el token sea válido.</p>";
        
        // Mostrar información de la configuración para depuración
        echo "<h3>Información de configuración:</h3>";
        echo "<ul>";
        echo "<li>URL: " . htmlspecialchars(MetricsFactory::getConfiguredUrl()) . "</li>";
        echo "<li>Organización: " . htmlspecialchars(MetricsFactory::getConfiguredOrg()) . "</li>";
        echo "<li>Bucket: " . htmlspecialchars(MetricsFactory::getConfiguredBucket()) . "</li>";
        echo "</ul>";
        echo "<p>Nota: El token no se muestra por seguridad</p>";
    }
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Traza del error:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}