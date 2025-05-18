<?php
/**
 * Herramienta de diagnóstico de rutas y assets
 */

// Habilitar el buffer de salida para capturar cualquier salida temprana
ob_start();

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar la sesión antes de cualquier salida
session_start();

// Cargar el autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: No se puede encontrar el archivo autoload.php.");
}
require_once $autoloadPath;

// Cargar la configuración
$configPath = __DIR__ . '/../app/config/config.php';
if (!file_exists($configPath)) {
    die("Error: No se puede encontrar el archivo de configuración.");
}
$config = require_once $configPath;

// Inicializar AssetHelper
\App\Helpers\AssetHelper::init($config);

// Función para verificar si un archivo existe
function verificarArchivo($ruta)
{
    $exists = file_exists($ruta);
    $readable = is_readable($ruta);
    $filesize = $exists ? filesize($ruta) : 0;
    return [
        'existe' => $exists,
        'legible' => $readable,
        'tamano' => $filesize,
        'ruta_completa' => $ruta
    ];
}

// Definir algunas rutas a verificar
$cssMain = $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/public/css/main.css';
$cssIndex = $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/public/css/index.css';
$jsMain = $_SERVER['DOCUMENT_ROOT'] . '/pruebas-ds/public/js/main.js';

// Rutas generadas por el helper
$cssUrl = \App\Helpers\AssetHelper::css('main');
$jsUrl = \App\Helpers\AssetHelper::js('main');
$homeUrl = \App\Helpers\AssetHelper::url();

// Verificar los archivos
$cssMainInfo = verificarArchivo($cssMain);
$cssIndexInfo = verificarArchivo($cssIndex);
$jsMainInfo = verificarArchivo($jsMain);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Assets - Café-VT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #8B4513;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        code {
            background: #f0f0f0;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico de Assets y Rutas</h1>
    
    <div class="box">
        <h2>Configuración</h2>
        <p><strong>URL Base:</strong> <?= $config['app']['url'] ?></p>
        <p><strong>Entorno:</strong> <?= $config['app']['env'] ?></p>
    </div>
    
    <h2>Rutas de Assets Generadas</h2>
    <table>
        <tr>
            <th>Tipo</th>
            <th>URL Generada</th>
        </tr>
        <tr>
            <td>CSS Principal</td>
            <td><code><?= htmlspecialchars($cssUrl) ?></code></td>
        </tr>
        <tr>
            <td>JavaScript Principal</td>
            <td><code><?= htmlspecialchars($jsUrl) ?></code></td>
        </tr>
        <tr>
            <td>URL de Inicio</td>
            <td><code><?= htmlspecialchars($homeUrl) ?></code></td>
        </tr>
    </table>
    
    <h2>Verificación de Archivos</h2>
    <table>
        <tr>
            <th>Archivo</th>
            <th>¿Existe?</th>
            <th>¿Legible?</th>
            <th>Tamaño</th>
            <th>Ruta Completa</th>
        </tr>
        <tr>
            <td>CSS Principal (main.css)</td>
            <td class="<?= $cssMainInfo['existe'] ? 'success' : 'error' ?>">
                <?= $cssMainInfo['existe'] ? 'SÍ' : 'NO' ?>
            </td>
            <td class="<?= $cssMainInfo['legible'] ? 'success' : 'error' ?>">
                <?= $cssMainInfo['legible'] ? 'SÍ' : 'NO' ?>
            </td>
            <td><?= $cssMainInfo['tamano'] ?> bytes</td>
            <td><code><?= htmlspecialchars($cssMainInfo['ruta_completa']) ?></code></td>
        </tr>
        <tr>
            <td>CSS Index (index.css)</td>
            <td class="<?= $cssIndexInfo['existe'] ? 'success' : 'error' ?>">
                <?= $cssIndexInfo['existe'] ? 'SÍ' : 'NO' ?>
            </td>
            <td class="<?= $cssIndexInfo['legible'] ? 'success' : 'error' ?>">
                <?= $cssIndexInfo['legible'] ? 'SÍ' : 'NO' ?>
            </td>
            <td><?= $cssIndexInfo['tamano'] ?> bytes</td>
            <td><code><?= htmlspecialchars($cssIndexInfo['ruta_completa']) ?></code></td>
        </tr>
        <tr>
            <td>JS Principal (main.js)</td>
            <td class="<?= $jsMainInfo['existe'] ? 'success' : 'error' ?>">
                <?= $jsMainInfo['existe'] ? 'SÍ' : 'NO' ?>
            </td>
            <td class="<?= $jsMainInfo['legible'] ? 'success' : 'error' ?>">
                <?= $jsMainInfo['legible'] ? 'SÍ' : 'NO' ?>
            </td>
            <td><?= $jsMainInfo['tamano'] ?> bytes</td>
            <td><code><?= htmlspecialchars($jsMainInfo['ruta_completa']) ?></code></td>
        </tr>
    </table>
    
    <h2>Información del Servidor</h2>
    <div class="box">
        <p><strong>DOCUMENT_ROOT:</strong> <code><?= $_SERVER['DOCUMENT_ROOT'] ?></code></p>
        <p><strong>REQUEST_URI:</strong> <code><?= $_SERVER['REQUEST_URI'] ?></code></p>
        <p><strong>SCRIPT_NAME:</strong> <code><?= $_SERVER['SCRIPT_NAME'] ?></code></p>
        <p><strong>PHP_SELF:</strong> <code><?= $_SERVER['PHP_SELF'] ?></code></p>
    </div>
    
    <h2>Prueba de Inclusión de CSS</h2>
    <p>Si ves un recuadro de color café abajo, significa que se ha cargado correctamente un estilo CSS:</p>
    
    <link rel="stylesheet" href="<?= \App\Helpers\AssetHelper::css('main') ?>">
    
    <div style="border: 1px solid #ddd; padding: 15px; margin-top: 10px;" class="section-title">
        <h3>Esto debería tener un estilo aplicado si main.css se carga correctamente</h3>
    </div>
    
    <h2>Solución de problemas comunes</h2>
    <div class="box">
        <p><strong>Si los archivos existen pero no son accesibles:</strong></p>
        <ul>
            <li>Revisa los permisos de los archivos (deberían ser 644 o 755)</li>
            <li>Verifica que Apache tenga acceso a esos directorios</li>
        </ul>
        
        <p><strong>Si las rutas generadas son incorrectas:</strong></p>
        <ul>
            <li>Ajusta la configuración de URL base en <code>app/config/config.php</code></li>
        </ul>
        
        <p><strong>Si los archivos no se encuentran en las rutas esperadas:</strong></p>
        <ul>
            <li>Asegúrate de que la estructura de directorios sea correcta</li>
            <li>Verifica que los archivos CSS y JS estén en las carpetas correspondientes</li>
        </ul>
    </div>
</body>
</html>
<?php
// Enviar salida al navegador
ob_end_flush();
?>