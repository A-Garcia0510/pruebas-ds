<?php
namespace App\Controllers;

use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Shop\Services\CartService;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Repositories\PurchaseRepository;
use App\Shop\Services\PurchaseService;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Shop\Exceptions\InsufficientStockException;

/**
 * Controlador para gestionar las operaciones del carrito de compras
 */
class CartController extends BaseController
{
    private $cartService;
    private $productRepository;
    private $purchaseRepository;
    private $purchaseService;
    
    /**
     * Constructor del controlador
     */
    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        
        try {
            // Log para depuración
            if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
                error_log('CartController::__construct() - Inicializando servicios');
            }
            
            // Inicializar servicios
            $dbConfig = new DatabaseConfiguration(
                $this->config['database']['host'],
                $this->config['database']['username'],
                $this->config['database']['password'],
                $this->config['database']['database']
            );
            
            $db = new MySQLDatabase($dbConfig);
            $this->productRepository = new ProductRepository($db);
            $this->cartService = new CartService($db, $this->productRepository);
            $this->purchaseRepository = new PurchaseRepository($db);
            $this->purchaseService = new PurchaseService($db, $this->cartService, $this->productRepository, $this->purchaseRepository);
            
            // Log para depuración
            if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
                error_log('CartController::__construct() - Servicios inicializados correctamente');
            }
        } catch (\Exception $e) {
            error_log('CartController::__construct() - Error al inicializar servicios: ' . $e->getMessage());
            // No lanzar excepción aquí para evitar errores en el constructor
        }
    }
    
    /**
     * Muestra la página del carrito
     */
    public function index()
    {
        // Log para depuración
        error_log('CartController::index() - Accediendo a la página del carrito');
        
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['correo'])) {
            // Redirigir con mensaje de sesión
            $_SESSION['message'] = 'Debes iniciar sesión para acceder al carrito';
            $_SESSION['message_type'] = 'error';
            error_log('CartController::index() - Usuario no autenticado, redirigiendo a login');
            $this->redirect('login');
            return;
        }
        
        return $this->render('cart/index');
    }
    
    /**
     * Obtiene los productos del carrito (para AJAX)
     */
    public function getItems()
    {
        // Log detallado de la sesión
        error_log('=== DEBUG CARRITO ===');
        error_log('Sesión completa: ' . print_r($_SESSION, true));
        error_log('Método de la petición: ' . $_SERVER['REQUEST_METHOD']);
        error_log('URL solicitada: ' . $_SERVER['REQUEST_URI']);
        error_log('===================');

        try {
            if (!isset($_SESSION['correo'])) {
                error_log('Usuario no autenticado - No existe $_SESSION[correo]');
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            $userEmail = $_SESSION['correo'];
            error_log("CartController::getItems() - Obteniendo carrito para usuario: " . $userEmail);
            
            $items = $this->cartService->getItems($userEmail);
            $total = $this->cartService->getTotal($userEmail);
            
            error_log("CartController::getItems() - Productos procesados: " . count($items));
            
            $response = [
                'success' => true,
                'carrito' => array_map(function($item) {
                    return [
                        'producto_ID' => $item->getProductId(),
                        'nombre_producto' => $item->getProductName(),
                        'cantidad' => $item->getQuantity(),
                        'precio' => $item->getProductPrice(),
                        'subtotal' => $item->getSubtotal()
                    ];
                }, $items),
                'total' => $total,
                'message' => count($items) > 0 ? 'Carrito obtenido exitosamente' : 'El carrito está vacío'
            ];
            
            error_log("CartController::getItems() - Respuesta: " . json_encode($response));
            $this->jsonResponse($response);
            
        } catch (\Exception $e) {
            error_log("CartController::getItems() - Error: " . $e->getMessage());
            error_log("CartController::getItems() - Stack trace: " . $e->getTraceAsString());
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener el carrito: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Añade un producto al carrito (para AJAX)
     */
    public function addItem()
    {
        try {
            if (!isset($_SESSION['correo'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_ID']) || !isset($data['cantidad'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ], 400);
                return;
            }

            $userEmail = $_SESSION['correo'];
            $productId = (int)$data['producto_ID'];
            $quantity = (int)$data['cantidad'];

            if ($quantity <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'La cantidad debe ser mayor a 0'
                ], 400);
                return;
            }

            $this->cartService->addItem($userEmail, $productId, $quantity);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Producto agregado al carrito exitosamente'
            ]);
            
        } catch (ProductNotFoundException $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'El producto no existe'
            ], 404);
        } catch (InsufficientStockException $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'producto_ID' => $e->getProductId(),
                'cantidad_solicitada' => $e->getRequestedQuantity(),
                'stock_disponible' => $e->getAvailableQuantity()
            ], 400);
        } catch (\Exception $e) {
            error_log("CartController::addItem() - Error: " . $e->getMessage());
            error_log("CartController::addItem() - Stack trace: " . $e->getTraceAsString());
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al agregar producto al carrito: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Elimina un producto del carrito (para AJAX)
     */
    public function removeItem()
    {
        error_log('CartController::removeItem() - Inicio de solicitud');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('CartController::removeItem() - Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            error_log('CartController::removeItem() - Usuario no logueado');
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            error_log('CartController::removeItem() - Datos recibidos: ' . json_encode($data));
            
            if (!isset($data['producto_ID'])) {
                error_log('CartController::removeItem() - ID de producto no válido');
                $this->jsonResponse(['success' => false, 'message' => 'ID de producto no válido.'], 400);
                return;
            }
            
            $productoID = $data['producto_ID'];
            $userEmail = $_SESSION['correo'];
            
            // Eliminar producto del carrito
            error_log("CartController::removeItem() - Eliminando producto ID: $productoID");
            $result = $this->cartService->removeItem($userEmail, $productoID);
            
            if ($result) {
                error_log('CartController::removeItem() - Producto eliminado exitosamente');
                $this->jsonResponse(['success' => true, 'message' => 'Producto eliminado del carrito.']);
            } else {
                error_log('CartController::removeItem() - No se pudo eliminar el producto');
                $this->jsonResponse(['success' => false, 'message' => 'No se pudo eliminar el producto del carrito.'], 400);
            }
        } catch (\Exception $e) {
            error_log('CartController::removeItem() - Error: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Finaliza la compra del carrito (para AJAX)
     */
    public function checkout()
    {
        error_log('CartController::checkout() - Inicio de solicitud');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('CartController::checkout() - Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            error_log('CartController::checkout() - Usuario no logueado');
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            $userEmail = $_SESSION['correo'];
            error_log('CartController::checkout() - Procesando compra para usuario: ' . $userEmail);
            
            // Verificar si el carrito tiene productos
            $cartItems = $this->cartService->getItems($userEmail);
            if (empty($cartItems)) {
                error_log('CartController::checkout() - Carrito vacío');
                $this->jsonResponse(['success' => false, 'message' => 'El carrito está vacío.'], 400);
                return;
            }
            
            // Crear la compra
            error_log('CartController::checkout() - Creando compra');
            $result = $this->purchaseService->createPurchase($userEmail);
            
            if ($result) {
                error_log('CartController::checkout() - Compra realizada con éxito');
                $this->jsonResponse(['success' => true, 'message' => 'Compra realizada con éxito.']);
            } else {
                error_log('CartController::checkout() - No se pudo procesar la compra');
                $this->jsonResponse(['success' => false, 'message' => 'No se pudo procesar la compra.'], 400);
            }
        } catch (\Exception $e) {
            error_log('CartController::checkout() - Error: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al procesar la compra: ' . $e->getMessage()], 500);
        }
    }

    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['correo']);
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}