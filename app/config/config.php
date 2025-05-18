<?php
// app/config/config.php

return [
    'app' => [
        'name' => 'Café-VT',
        'url' => '', // Dejamos vacío para detectar automáticamente o configurar manualmente según el entorno
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
    ]
];