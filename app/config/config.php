<?php
// Configuración actualizada para app/config/config.php

return [
    'app' => [
        'name' => 'Café-VT',
        'url' => '', // Dejamos vacío para detectar automáticamente
        'env' => 'development', // 'development' o 'production'
        'debug' => true, // Habilitamos el modo debug para ver errores
        'display_errors' => true, // Mostrar errores PHP
    ],
    
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'ethos_bd'
    ],
    
    // Puedes agregar más configuraciones según sea necesario
    'email' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'noreply@ejemplo.com',
        'password' => 'your-email-password',
        'encryption' => 'tls',
        'from_name' => 'Café-VT',
    ],
    
    'session' => [
        'lifetime' => 7200, // 2 horas en segundos
        'secure' => false, // Cambiar a true en producción con HTTPS
        'httponly' => true,
    ],
    
    // Configuración específica para assets
    'assets' => [
        // Si quieres forzar una URL base para los activos, configúrala aquí
        // De lo contrario, se detectará automáticamente
        'base_url' => '',
        // Por si necesitas una ruta diferente para los CSS
        'css_path' => '/css',
        // Por si necesitas una ruta diferente para los JS
        'js_path' => '/js',
        // Por si necesitas una ruta diferente para las imágenes
        'img_path' => '/img',
    ]
];