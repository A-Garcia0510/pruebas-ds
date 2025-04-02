<?php
// PHP/finalizar_compra.php
session_start();
require_once 'autoload.php';

use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Shop\Services\CartService;
use App\Shop\Services\PurchaseService;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Repositories\PurchaseRepository;
use App\Shop\Exceptions\CheckoutException;
use App\Shop\Exceptions\InsufficientStockException;
use App\Shop\Exceptions\ProductNotFoundException;

// Verificar si el usuario está autenticado
if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];

try {
    // Crear las dependencias necesarias
    $correoUsuario = $_SESSION['correo'];
    
    // Cargar configuración de la base de datos
    $config = require_once '../src/Config/Config.php';
    $dbConfig = new DatabaseConfiguration(
        $config['database']['host'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['database']
    );
    
    
    $database = new MySQLDatabase($dbConfig);
    
    // Crear los repositorios y servicios
    $productRepository = new ProductRepository($database);
    $purchaseRepository = new PurchaseRepository($database);
    $cartService = new CartService($database, $productRepository);
    $purchaseService = new PurchaseService(
        $database,
        $cartService,
        $productRepository,
        $purchaseRepository
    );
    
    // Procesar la compra
    $result = $purchaseService->createPurchase($correoUsuario);
    
    echo json_encode([
        'success' => true,
        'message' => 'Compra realizada con éxito.'
    ]);
    
} catch (InsufficientStockException $e) {
    // Error específico de stock insuficiente
    echo json_encode([
        'success' => false,
        'error_type' => 'insufficient_stock',
        'message' => $e->getMessage(),
        'product_id' => $e->getProductId(),
        'requested' => $e->getRequestedQuantity(),
        'available' => $e->getAvailableQuantity()
    ]);
} catch (ProductNotFoundException $e) {
    // Error de producto no encontrado
    echo json_encode([
        'success' => false,
        'error_type' => 'product_not_found',
        'message' => $e->getMessage()
    ]);
} catch (CheckoutException $e) {
    // Error durante el proceso de checkout
    echo json_encode([
        'success' => false,
        'error_type' => 'checkout_error',
        'message' => $e->getMessage(),
        'cart_items' => $e->getCartItems()
    ]);
} catch (\Exception $e) {
    // Otros errores
    echo json_encode([
        'success' => false,
        'error_type' => 'general_error',
        'message' => 'Ha ocurrido un error inesperado: ' . $e->getMessage()
    ]);
}