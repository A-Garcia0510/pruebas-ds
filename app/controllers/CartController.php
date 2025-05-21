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
use App\Shop\Services\ProductService;
use App\Shop\Commands\CartCommandInvoker;
use App\Shop\Commands\AddToCartCommand;
use App\Shop\Commands\RemoveFromCartCommand;

/**
 * Controlador para gestionar las operaciones del carrito de compras
 */
class CartController extends BaseController
{
    private $cartService;
    private $productRepository;
    private $purchaseRepository;
    private $purchaseService;
    private $productService;
    private $commandInvoker;
    
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
            $this->productService = new ProductService($this->productRepository);
            $this->commandInvoker = new CartCommandInvoker($this->cartService);
            
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
        
        return $this->render('cart/index', [
            'title' => 'Carrito de Compras - Café-VT',
            'css' => ['carro']
        ]);
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
            // Verificar si el usuario está autenticado
            if (!isset($_SESSION['correo'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Debes iniciar sesión para agregar productos al carrito']);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_ID']) || !isset($data['cantidad'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
            }

            $productoId = $data['producto_ID'];
            $cantidad = (int)$data['cantidad'];
            $actualizar = isset($data['actualizar']) ? $data['actualizar'] : false;

            if ($cantidad < 1) {
                return $this->jsonResponse(['success' => false, 'message' => 'La cantidad debe ser mayor a 0']);
            }

            // Verificar stock disponible
            $producto = $this->productRepository->findById($productoId);
            if (!$producto) {
                return $this->jsonResponse(['success' => false, 'message' => 'Producto no encontrado']);
            }

            if ($producto->getStock() < $cantidad) {
                return $this->jsonResponse(['success' => false, 'message' => 'Stock insuficiente']);
            }

            // Crear el comando
            $command = new AddToCartCommand(
                $this->cartService,
                $_SESSION['correo'],
                $productoId,
                $cantidad,
                $actualizar
            );

            // Ejecutar el comando
            $result = $this->commandInvoker->executeCommand($command);

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => 'Producto agregado al carrito']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Error al agregar el producto al carrito']);
            }
        } catch (\Exception $e) {
            error_log("Error en addItem: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al procesar la solicitud']);
        }
    }
    
    /**
     * Elimina un producto del carrito (para AJAX)
     */
    public function removeItem()
    {
        try {
            if (!isset($_SESSION['correo'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_ID'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID de producto no válido'
                ], 400);
            }
            
            $userEmail = $_SESSION['correo'];
            $productId = (int)$data['producto_ID'];

            // Obtener la cantidad actual del producto en el carrito
            $cartItems = $this->cartService->getItems($userEmail);
            $quantity = 0;
            foreach ($cartItems as $item) {
                if ($item->getProductId() == $productId) {
                    $quantity = $item->getQuantity();
                    break;
                }
            }

            $command = new RemoveFromCartCommand($this->cartService, $userEmail, $productId, $quantity);
            if ($this->commandInvoker->executeCommand($command)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Producto eliminado del carrito'
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al eliminar el producto'
            ], 500);
            
        } catch (\Exception $e) {
            error_log("CartController::removeItem() - Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
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

    public function undoLastAction() {
        try {
            if (!isset($_SESSION['correo'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            if ($this->commandInvoker->undoLastCommand()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Última acción deshecha'
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'No hay acciones para deshacer'
            ], 400);
            
        } catch (\Exception $e) {
            error_log("CartController::undoLastAction() - Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al deshacer la acción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function redoLastAction() {
        try {
            if (!isset($_SESSION['correo'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            if ($this->commandInvoker->redoLastCommand()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Última acción rehecha'
                ]);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'No hay acciones para rehacer'
            ], 400);
            
        } catch (\Exception $e) {
            error_log("CartController::redoLastAction() - Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al rehacer la acción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el estado del historial de acciones del carrito
     */
    public function history()
    {
        try {
            if (!isset($_SESSION['correo'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            }

            // Obtener el estado actual del historial de comandos
            $hasUndoActions = $this->commandInvoker->hasUndoActions();
            $hasRedoActions = $this->commandInvoker->hasRedoActions();

            return $this->jsonResponse([
                'success' => true,
                'hasUndoActions' => $hasUndoActions,
                'hasRedoActions' => $hasRedoActions
            ]);
        } catch (\Exception $e) {
            error_log("CartController::history() - Error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
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