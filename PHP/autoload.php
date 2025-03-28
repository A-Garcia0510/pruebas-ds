<?php
spl_autoload_register(function ($class) {
    // Replace namespace separators with directory separators
    $class = str_replace('\\', '/', $class);
    
    // Base directory for classes
    $baseDir = __DIR__ . '/classes/';
    
    // Possible file paths
    $possibilities = [
        $baseDir . $class . '.php',
        $baseDir . str_replace('Interfaces/', 'Interfaces/', $class) . '.php',
        $baseDir . str_replace('Entities/', 'Entities/', $class) . '.php',
        $baseDir . str_replace('Exceptions/', 'Exceptions/', $class) . '.php',
        $baseDir . str_replace('Repositories/', 'Repositories/', $class) . '.php',
        $baseDir . str_replace('Services/', 'Services/', $class) . '.php'
    ];
    
    // Try to load the file
    foreach ($possibilities as $file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});
