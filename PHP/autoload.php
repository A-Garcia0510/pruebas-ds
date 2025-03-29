<?php
// autoload.php
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    
    // Verificar si la clase usa el prefijo
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, muévete al siguiente autoloader registrado
        return;
    }
    
    // Obtener la ruta relativa de la clase
    $relative_class = substr($class, $len);
    
    // Reemplazar namespace separadores con separadores de directorio
    // Añadir .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Si el archivo existe, requerirlo
    if (file_exists($file)) {
        require $file;
    }
});
