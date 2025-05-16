<?php
// autoload.php
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $prefixes = [
        'App\\' => __DIR__ . '/../src/',
        'Metrics\\' => __DIR__ . '/../src/metrics/',
        'PHP\\Commands\\' => __DIR__ . '/Commands/'
    ];
    
    // Buscar en cada prefijo registrado
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue; // Esta clase no usa este prefijo, probar el siguiente
        }
        
        // Obtener la ruta relativa de la clase
        $relative_class = substr($class, $len);
        
        // Reemplazar namespace separadores con separadores de directorio
        // Añadir .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // Si el archivo existe, requerirlo
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});