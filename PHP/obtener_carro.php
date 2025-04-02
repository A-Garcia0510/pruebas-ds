<?php
session_start();
require_once 'autoload.php';

use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Shop\Services\CartService;
use App\Shop\Repositories\ProductRepository;

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

try {
    $correoUsuario = $_SESSION['correo'];
    
    // Cargar configuración de la base de datos
    $config = require_once '../src/Config/Config.php';
    $dbConfig = new DatabaseConfiguration(
        $config['database']['host'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['database']
    );
    
    // Inicializar dependencias
    $db = new MySQLDatabase($dbConfig);
    $productRepository = new ProductRepository($db);
    $cartService = new CartService($db, $productRepository);
    
    // Obtener los productos del carrito
    $cartItems = $cartService->getItems($correoUsuario);
    
    // Transformar los objetos CartItem a arrays para JSON
    $carrito = [];
    $total = 0;
    
    foreach ($cartItems as $item) {
        $subtotal = $item->getSubtotal();
        $total += $subtotal;
        
        $carrito[] = [
            'producto_ID' => $item->getProductId(),
            'nombre_producto' => $item->getProductName(),
            'cantidad' => $item->getQuantity(),
            'precio' => $item->getProductPrice(),
            'subtotal' => $subtotal
        ];
    }
    
    if (!empty($carrito)) {
        echo json_encode([
            'success' => true, 
            'carrito' => $carrito,
            'total' => $total
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
    }
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener carrito: ' . $e->getMessage()]);
}