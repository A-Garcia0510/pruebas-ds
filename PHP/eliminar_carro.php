<?php
session_start();
require_once 'autoload.php';

use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Shop\Services\CartService;
use App\Shop\Repositories\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

try {
    // Obtener datos de la solicitud
    $data = json_decode(file_get_contents('php://input'));
    $productoID = $data->producto_ID;
    $correoUsuario = $_SESSION['correo'];
    
    if (!isset($productoID)) {
        echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
        exit();
    }
    
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
    
    // Eliminar producto del carrito
    $result = $cartService->removeItem($correoUsuario, $productoID);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el producto del carrito.']);
    }
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()]);
}