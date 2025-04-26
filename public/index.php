<?php
// public/index.php

// Establecer el directorio raíz
define('ROOT_DIR', dirname(__DIR__));

// Configurar el autoload
require_once ROOT_DIR . '/vendor/autoload.php';

// Iniciar monitor de carga (opcional)
if (file_exists(ROOT_DIR . '/PHP/load_monitor.php')) {
    require_once ROOT_DIR . '/PHP/load_monitor.php';
    $monitor = new LoadMonitor();
}

// Iniciar la sesión
session_start();

// Iniciar la aplicación
$app = new \App\Core\App();
$app->run();

// Finalizar monitor (opcional)
if (isset($monitor)) {
    $monitor->finalize(http_response_code());
    
    // Mostrar métricas en modo debug
    if (isset($_GET['debug_metrics']) && $_GET['debug_metrics'] === 'true') {
        echo '<div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-top: 20px;">';
        echo '<h3>Métricas de Carga (Solo Debug)</h3>';
        echo '<pre>';
        print_r($monitor->getMetrics());
        echo '</pre>';
        echo '</div>';
    }
}