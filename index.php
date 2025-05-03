<?php
/**
 * Punto de entrada principal de la aplicación Ethos Coffe
 * Este archivo inicializa la aplicación y dirige las solicitudes al controlador apropiado
 */

// Cargar el monitor de rendimiento si existe
if (file_exists(__DIR__ . '/PHP/load_monitor.php')) {
    require_once __DIR__ . '/PHP/load_monitor.php';
    $monitor = new LoadMonitor();
}

// Iniciar la sesión
session_start();

// Cargar el Router
require_once __DIR__ . '/src/core/Router.php';

// Inicializar el router y procesar la solicitud
$router = new Router();
$router->dispatch();

// Finalizar el monitoreo al terminar la página si existe
if (isset($monitor)) {
    $monitor->finalize(http_response_code());
    
    // Si estamos en modo de prueba, mostrar las métricas (solo para desarrollo)
    if (isset($_GET['debug_metrics']) && $_GET['debug_metrics'] === 'true') {
        echo '<div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-top: 20px;">';
        echo '<h3>Métricas de Carga (Solo Debug)</h3>';
        echo '<pre>';
        print_r($monitor->getMetrics());
        echo '</pre>';
        echo '</div>';
    }
}
?>