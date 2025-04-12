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
        
        // Comprobar si la base de datos existe
        echo "<p>Verificando si la base de datos existe...</p>";
        
        try {
            $databases = $influxClient->query('SHOW DATABASES');
            
            if (empty($databases)) {
                echo "<p style='color: orange;'>No se pudieron obtener las bases de datos o la lista está vacía.</p>";
                echo "<p>Mensaje: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
                
                // Intentar crear la base de datos directamente
                echo "<p>Intentando crear la base de datos directamente...</p>";
                $createResult = $influxClient->query('CREATE DATABASE ' . MetricsFactory::getConfiguredDB());
                if (!empty($createResult) && isset($createResult[0]['success']) && $createResult[0]['success']) {
                    echo "<p style='color: green;'>✓ Base de datos creada correctamente.</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ No se pudo confirmar la creación de la base de datos.</p>";
                }
            } else {
                echo "<p>Bases de datos disponibles:</p>";
                echo "<ul>";
                $dbExists = false;
                foreach ($databases as $db) {
                    if (isset($db['name'])) {
                        echo "<li>" . htmlspecialchars($db['name']) . "</li>";
                        if ($db['name'] === MetricsFactory::getConfiguredDB()) {
                            $dbExists = true;
                        }
                    }
                }
                echo "</ul>";
                
                if (!$dbExists) {
                    echo "<p style='color: orange;'>⚠ La base de datos '" . htmlspecialchars(MetricsFactory::getConfiguredDB()) . "' no existe. Esto podría ser la causa del error.</p>";
                    echo "<p>Intentando crear la base de datos...</p>";
                    $createResult = $influxClient->query('CREATE DATABASE ' . MetricsFactory::getConfiguredDB());
                    if (!empty($createResult) && isset($createResult[0]['success']) && $createResult[0]['success']) {
                        echo "<p style='color: green;'>✓ Base de datos creada correctamente.</p>";
                    } else {
                        echo "<p style='color: orange;'>⚠ No se pudo confirmar la creación de la base de datos.</p>";
                    }
                }
            }
        } catch (Exception $dbError) {
            echo "<p style='color: orange;'>Error al consultar bases de datos: " . htmlspecialchars($dbError->getMessage()) . "</p>";
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
            
            // Verificar que el dato se escribió correctamente
            echo "<p>Verificando que el dato se ha escrito correctamente...</p>";
            $query = 'SELECT * FROM test_measurement WHERE source=\'test_script\' ORDER BY time DESC LIMIT 1';
            $data = $influxClient->query($query);
            
            if (!empty($data)) {
                echo "<p style='color: green;'>✓ Dato verificado correctamente:</p>";
                echo "<pre>" . print_r($data[0], true) . "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠ No se pudo verificar el dato.</p>";
                echo "<p>Error: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
                echo "<p>Esto puede deberse a que la consulta es incorrecta o la base de datos no contiene los datos aún.</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Conexión establecida pero error al escribir datos.</p>";
            echo "<p>Error: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
            
            // Comprobar permisos del usuario
            echo "<p>Verificando permisos del usuario...</p>";
            echo "<p>Este error puede ocurrir si el usuario '" . htmlspecialchars(MetricsFactory::getConfiguredUser()) . "' no tiene permisos para escribir en la base de datos '" . htmlspecialchars(MetricsFactory::getConfiguredDB()) . "'.</p>";
        }
    } else {
        echo "<h2 style='color: red;'>❌ No se pudo conectar a InfluxDB.</h2>";
        echo "<p>Error: " . htmlspecialchars($influxClient->getLastError()) . "</p>";
        echo "<p>Verifica que InfluxDB esté funcionando en la URL configurada y que las credenciales sean correctas.</p>";
        
        // Mostrar información de la configuración para depuración
        echo "<h3>Información de configuración:</h3>";
        echo "<ul>";
        echo "<li>URL: " . htmlspecialchars(MetricsFactory::getConfiguredUrl()) . "</li>";
        echo "<li>Usuario: " . htmlspecialchars(MetricsFactory::getConfiguredUser()) . "</li>";
        echo "<li>Base de datos: " . htmlspecialchars(MetricsFactory::getConfiguredDB()) . "</li>";
        echo "</ul>";
        echo "<p>Nota: Las contraseñas no se muestran por seguridad</p>";
    }
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p>Traza del error:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}