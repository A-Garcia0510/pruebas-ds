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
        error_log('CartController::getItems() - Inicio de solicitud');
        
        if (!isset($_SESSION['correo'])) {
            error_log('CartController::getItems() - Usuario no logueado');
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            $correoUsuario = $_SESSION['correo'];
            error_log('CartController::getItems() - Obteniendo carrito para usuario: ' . $correoUsuario);
            
            $cartItems = $this->cartService->getItems($correoUsuario);
            
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
                error_log('CartController::getItems() - Carrito obtenido con éxito. Productos: ' . count($carrito));
                $this->json([
                    'success' => true, 
                    'carrito' => $carrito,
                    'total' => $total
                ]);
            } else {
                error_log('CartController::getItems() - Carrito vacío');
                $this->json(['success' => true, 'carrito' => [], 'message' => 'El carrito está vacío.']);
            }
        } catch (\Exception $e) {
            error_log('CartController::getItems() - Error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al obtener carrito: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Añade un producto al carrito (para AJAX)
     */
    public function addItem()
    {
        error_log('CartController::addItem() - Inicio de solicitud');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('CartController::addItem() - Método no permitido: ' . $_SERVER['REQUEST_METHOD']);
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            error_log('CartController::addItem() - Usuario no logueado');
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            error_log('CartController::addItem() - Datos recibidos: ' . json_encode($data));
            
            if (!isset($data['producto_ID']) || !isset($data['cantidad'])) {
                error_log('CartController::addItem() - Faltan datos requeridos');
                $this->json(['success' => false, 'message' => 'Faltan datos requeridos.'], 400);
                return;
            }
            
            $productoID = $data['producto_ID'];
            $cantidad = $data['cantidad'];
            $correoUsuario = $_SESSION['correo'];
            
            // Agregar producto al carrito
            error_log("CartController::addItem() - Agregando producto ID: $productoID, cantidad: $cantidad");
            $result = $this->cartService->addItem($correoUsuario, $productoID, $cantidad);
            
            error_log('CartController::addItem() - Producto agregado exitosamente');
            $this->json(['success' => true, 'message' => 'Producto agregado al carrito.']);
            
        } catch (ProductNotFoundException $e) {
            error_log('CartController::addItem() - Producto no encontrado: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (InsufficientStockException $e) {
            error_log('CartController::addItem() - Stock insuficiente: ' . $e->getMessage());
            $this->json([
                'success' => false, 
                'message' => $e->getMessage(),
                'productId' => $e->getProductId(),
                'requestedQuantity' => $e->getRequestedQuantity(),
                'availableQuantity' => $e->getAvailableQuantity()
            ], 400);
        } catch (\Exception $e) {
            error_log('CartController::addItem() - Error general: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al agregar producto: ' . $e->getMessage()], 500);
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
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            error_log('CartController::removeItem() - Usuario no logueado');
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            error_log('CartController::removeItem() - Datos recibidos: ' . json_encode($data));
            
            if (!isset($data['producto_ID'])) {
                error_log('CartController::removeItem() - ID de producto no válido');
                $this->json(['success' => false, 'message' => 'ID de producto no válido.'], 400);
                return;
            }
            
            $productoID = $data['producto_ID'];
            $correoUsuario = $_SESSION['correo'];
            
            // Eliminar producto del carrito
            error_log("CartController::removeItem() - Eliminando producto ID: $productoID");
            $result = $this->cartService->removeItem($correoUsuario, $productoID);
            
            if ($result) {
                error_log('CartController::removeItem() - Producto eliminado exitosamente');
                $this->json(['success' => true, 'message' => 'Producto eliminado del carrito.']);
            } else {
                error_log('CartController::removeItem() - No se pudo eliminar el producto');
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el producto del carrito.'], 400);
            }
        } catch (\Exception $e) {
            error_log('CartController::removeItem() - Error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()], 500);
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
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            error_log('CartController::checkout() - Usuario no logueado');
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            $correoUsuario = $_SESSION['correo'];
            error_log('CartController::checkout() - Procesando compra para usuario: ' . $correoUsuario);
            
            // Verificar si el carrito tiene productos
            $cartItems = $this->cartService->getItems($correoUsuario);
            if (empty($cartItems)) {
                error_log('CartController::checkout() - Carrito vacío');
                $this->json(['success' => false, 'message' => 'El carrito está vacío.'], 400);
                return;
            }
            
            // Crear la compra
            error_log('CartController::checkout() - Creando compra');
            $result = $this->purchaseService->createPurchase($correoUsuario);
            
            if ($result) {
                error_log('CartController::checkout() - Compra realizada con éxito');
                $this->json(['success' => true, 'message' => 'Compra realizada con éxito.']);
            } else {
                error_log('CartController::checkout() - No se pudo procesar la compra');
                $this->json(['success' => false, 'message' => 'No se pudo procesar la compra.'], 400);
            }
        } catch (\Exception $e) {
            error_log('CartController::checkout() - Error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error al procesar la compra: ' . $e->getMessage()], 500);
        }
    }
}