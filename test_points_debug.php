<?php
session_start();

// Simular un usuario logueado
$_SESSION['user_id'] = 1;
$_SESSION['nombre'] = 'Test User';
$_SESSION['correo'] = 'test@example.com';

function shutdownHandler() {
    $error = error_get_last();
    if ($error !== null) {
        echo "\n❌ ERROR FATAL: {$error['message']}\nArchivo: {$error['file']}\nLínea: {$error['line']}\n";
    }
    @ob_flush();
    @flush();
}
register_shutdown_function('shutdownHandler');

ob_start();
echo "=== PRUEBA DE OTORGAMIENTO DE PUNTOS ===\n";
echo "Usuario ID: " . $_SESSION['user_id'] . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

// Incluir dependencias necesarias
require_once 'app/core/interfaces/RequestInterface.php';
require_once 'app/core/interfaces/ResponseInterface.php';
require_once 'app/core/Request.php';
require_once 'app/core/Response.php';
require_once 'app/core/Container.php';
require_once 'app/controllers/BaseController.php';
require_once 'app/controllers/LoyaltyController.php';

use App\Controllers\LoyaltyController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Container;

try {
    // Crear instancias necesarias
    $request = new Request();
    $response = new Response();
    $container = new Container();

    // Cargar configuración y hacer binding manual
    $config = require 'app/config/config.php';
    $container->bind('config', function() use ($config) { return $config; });
    
    // Crear el controlador
    $loyaltyController = new LoyaltyController($request, $response, $container);
    
    echo "1. Verificando estado de la API...\n";
    $apiStatus = $loyaltyController->isApiAvailable();
    echo "API disponible: " . ($apiStatus ? 'SÍ' : 'NO') . "\n\n";
    
    if (!$apiStatus) {
        echo "❌ ERROR: La API no está disponible\n";
        exit(1);
    }
    
    echo "2. Probando otorgamiento de puntos...\n";
    $user_id = 1;
    $amount = 5000; // $5,000 CLP
    $description = "Prueba de puntos desde script de debug";
    
    echo "Usuario: $user_id\n";
    echo "Monto: $" . number_format($amount, 0, ',', '.') . " CLP\n";
    echo "Puntos esperados: " . floor($amount / 100) . "\n\n";
    
    // Generar múltiples transacciones para probar el historial
    $transactions = [
        ['amount' => 3000, 'description' => 'Compra de café americano'],
        ['amount' => 2500, 'description' => 'Compra de cappuccino'],
        ['amount' => 4000, 'description' => 'Compra de frappuccino'],
        ['amount' => 1500, 'description' => 'Compra de té chai']
    ];
    
    foreach ($transactions as $i => $tx) {
        echo "Transacción " . ($i + 1) . ": $" . number_format($tx['amount'], 0, ',', '.') . " CLP\n";
        $result = $loyaltyController->awardPointsForPurchase($user_id, $tx['amount'], $tx['description']);
        if ($result['success']) {
            echo "✓ Puntos otorgados: " . $result['points_earned'] . "\n";
        } else {
            echo "✗ Error: " . $result['message'] . "\n";
        }
        echo "\n";
    }
    
    echo "\n4. Verificando perfil del usuario...\n";
    $profile = $loyaltyController->getUserProfile($user_id);
    echo "Puntos actuales: " . ($profile['total_points'] ?? 'N/A') . "\n";
    echo "Rango actual: " . ($profile['current_tier'] ?? 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
@ob_flush();
@flush();
?> 