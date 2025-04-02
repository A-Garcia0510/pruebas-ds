<?php
session_start();
require_once 'autoload.php';

use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Shop\Services\CartService;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Shop\Exceptions\InsufficientStockException;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['correo'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
        exit();
    }
    
    try {
        // Obtener datos de la solicitud
        $data = json_decode(file_get_contents('php://input'), true);
        $productoID = $data['producto_ID'];
        $cantidad = $data['cantidad'];
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
        
        // Agregar producto al carrito
        $result = $cartService->addItem($correoUsuario, $productoID, $cantidad);
        
        echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito.']);
    } catch (ProductNotFoundException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (InsufficientStockException $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage(),
            'productId' => $e->getProductId(),
            'requestedQuantity' => $e->getRequestedQuantity(),
            'availableQuantity' => $e->getAvailableQuantity()
        ]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al agregar producto: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}