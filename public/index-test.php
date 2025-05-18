<?php
// Este es un archivo de prueba para verificar la carga de recursos estáticos
$baseUrl = "http://localhost/pruebas-ds"; // Ajusta esta URL según tu configuración
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de CSS - Café-VT</title>
    <!-- Prueba de carga de CSS con diferentes métodos -->
    <link rel="stylesheet" href="/css/main.css"> <!-- Ruta absoluta desde raíz del servidor -->
    <link rel="stylesheet" href="css/main.css"> <!-- Ruta relativa -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css"> <!-- URL completa -->
    
    <style>
        /* Estilos básicos para ver si el HTML se carga correctamente */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .test-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
        }
        h1 {
            color: blue;
        }
    </style>
</head>
<body>
    <h1>Prueba de Carga de CSS</h1>
    
    <div class="test-box">
        <h2>Test de carga de CSS</h2>
        <p>Esta página es para verificar si la carga de CSS funciona correctamente.</p>
        <p>Si ves este texto con el estilo de .test-box, significa que el HTML y CSS interno funcionan.</p>
    </div>
    
    <div class="section">
        <h2>Elemento con clase 'section' del CSS principal</h2>
        <p>Si este elemento tiene el estilo adecuado, significa que el archivo main.css se ha cargado correctamente.</p>
    </div>
    
    <div>
        <h3>Información del servidor:</h3>
        <pre>
            <?php
            echo "URL Base configurada: " . $baseUrl . "\n";
            echo "Ruta del script: " . $_SERVER['SCRIPT_NAME'] . "\n";
            echo "Directorio del script: " . dirname($_SERVER['SCRIPT_NAME']) . "\n";
            echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
            echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
            
            // Verificar si el archivo CSS existe
            $cssPath = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . '/css/main.css';
            echo "Ruta completa del CSS: " . $cssPath . "\n";
            echo "¿El archivo CSS existe? " . (file_exists($cssPath) ? "SÍ" : "NO") . "\n";
            ?>
        </pre>
    </div>
</body>
</html>