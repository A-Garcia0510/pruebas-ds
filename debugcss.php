<?php
/**
 * Herramienta de diagnóstico para CSS
 * Coloca este archivo en la raíz de tu proyecto y accede a él desde el navegador
 */

// Verificar si se puede acceder al archivo CSS principal
$cssPath = $_SERVER['DOCUMENT_ROOT'] . '/CSS/index.css';
$cssRelativePath = '/CSS/index.css';

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico CSS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h1 { color: #5D4037; }
        h2 { color: #795548; margin-top: 30px; }
        .success { color: green; }
        .error { color: red; }
        .code {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
        }
        .test {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de CSS para Ethos Coffe</h1>
        
        <div class="test">
            <h2>1. Prueba de acceso al archivo CSS principal</h2>';

if (file_exists($cssPath)) {
    echo '<p class="success">✅ El archivo CSS existe en: ' . $cssPath . '</p>';
    echo '<p>Tamaño del archivo: ' . filesize($cssPath) . ' bytes</p>';
    
    // Mostrar las primeras líneas del CSS
    $cssContent = file_get_contents($cssPath);
    $cssPreview = substr($cssContent, 0, 500) . (strlen($cssContent) > 500 ? '...' : '');
    
    echo '<p>Vista previa del CSS:</p>';
    echo '<div class="code">' . htmlspecialchars($cssPreview) . '</div>';
} else {
    echo '<p class="error">❌ El archivo CSS no existe en: ' . $cssPath . '</p>';
    echo '<p>Verifica la ruta del archivo CSS en tu proyecto.</p>';
}

echo '</div>
        
        <div class="test">
            <h2>2. Información del servidor</h2>
            <p><strong>DOCUMENT_ROOT:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</p>
            <p><strong>SCRIPT_NAME:</strong> ' . $_SERVER['SCRIPT_NAME'] . '</p>
            <p><strong>PHP_SELF:</strong> ' . $_SERVER['PHP_SELF'] . '</p>
            <p><strong>REQUEST_URI:</strong> ' . $_SERVER['REQUEST_URI'] . '</p>
        </div>
        
        <div class="test">
            <h2>3. Prueba de enlace de CSS</h2>
            <p>Probaremos si el navegador puede acceder al CSS:</p>';

// Genera un ID único para evitar cachés
$uniqueId = uniqid();
echo '<p><a href="' . $cssRelativePath . '?v=' . $uniqueId . '" target="_blank">Abrir archivo CSS en una nueva pestaña</a></p>';
echo '<p>Si se muestra el contenido del CSS, la ruta es accesible.</p>';

echo '</div>
        
        <div class="test">
            <h2>4. Prueba con HTML en línea</h2>
            <p>Intentaremos cargar el CSS directamente en un fragmento HTML:</p>';

echo '<iframe src="about:blank" id="testFrame" style="width:100%; height:100px; border:1px solid #ddd;"></iframe>';
echo '<script>
    // Crear un documento HTML simple con el CSS
    const frameDoc = document.getElementById("testFrame").contentWindow.document;
    frameDoc.open();
    frameDoc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <link rel="stylesheet" href="' . $cssRelativePath . '?v=' . $uniqueId . '">
            <style>
                body { padding: 10px; }
                p { margin: 0; }
            </style>
        </head>
        <body>
            <p>Este es un texto de prueba. Si el CSS se carga correctamente, debería verse con los estilos de Ethos Coffe.</p>
        </body>
        </html>
    `);
    frameDoc.close();
</script>';

echo '</div>
        
        <div class="test">
            <h2>5. Sugerencias para solucionar problemas</h2>
            <ul>
                <li>Asegúrate de que la carpeta CSS esté en la raíz del proyecto</li>
                <li>Verifica los permisos de lectura de los archivos CSS</li>
                <li>Usa rutas absolutas en los enlaces de CSS (comenzando con /)</li>
                <li>Verifica que no haya errores de sintaxis en el CSS</li>
                <li>Prueba agregando este CSS en línea en el header.php para debugging:</li>
            </ul>
            <div class="code">
&lt;style&gt;
/* Estilos mínimos para debugging */
body { 
    font-family: Arial, sans-serif; 
    line-height: 1.6;
    margin: 0;
    padding: 0;
}
.navbar {
    background-color: #5D4037;
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.nav-links {
    display: flex;
    list-style: none;
}
.nav-links li {
    margin-left: 20px;
}
.nav-links a {
    color: white;
    text-decoration: none;
}
&lt;/style&gt;
            </div>
        </div>
    </div>
</body>
</html>';
?>