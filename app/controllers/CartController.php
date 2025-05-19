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
    }
    
    /**
     * Muestra la página del carrito
     */
    public function index()
    {
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['correo'])) {
            // Redirigir con mensaje de sesión
            $_SESSION['message'] = 'Debes iniciar sesión para acceder al carrito';
            $_SESSION['message_type'] = 'error';
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
        if (!isset($_SESSION['correo'])) {
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            $correoUsuario = $_SESSION['correo'];
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
                $this->json([
                    'success' => true, 
                    'carrito' => $carrito,
                    'total' => $total
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'El carrito está vacío.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error al obtener carrito: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Añade un producto al carrito (para AJAX)
     */
    public function addItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_ID']) || !isset($data['cantidad'])) {
                $this->json(['success' => false, 'message' => 'Faltan datos requeridos.'], 400);
                return;
            }
            
            $productoID = $data['producto_ID'];
            $cantidad = $data['cantidad'];
            $correoUsuario = $_SESSION['correo'];
            
            // Agregar producto al carrito
            $result = $this->cartService->addItem($correoUsuario, $productoID, $cantidad);
            
            $this->json(['success' => true, 'message' => 'Producto agregado al carrito.']);
            
        } catch (ProductNotFoundException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (InsufficientStockException $e) {
            $this->json([
                'success' => false, 
                'message' => $e->getMessage(),
                'productId' => $e->getProductId(),
                'requestedQuantity' => $e->getRequestedQuantity(),
                'availableQuantity' => $e->getAvailableQuantity()
            ], 400);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error al agregar producto: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Elimina un producto del carrito (para AJAX)
     */
    public function removeItem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            // Obtener datos de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['producto_ID'])) {
                $this->json(['success' => false, 'message' => 'ID de producto no válido.'], 400);
                return;
            }
            
            $productoID = $data['producto_ID'];
            $correoUsuario = $_SESSION['correo'];
            
            // Eliminar producto del carrito
            $result = $this->cartService->removeItem($correoUsuario, $productoID);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Producto eliminado del carrito.']);
            } else {
                $this->json(['success' => false, 'message' => 'No se pudo eliminar el producto del carrito.'], 400);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error al eliminar producto: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Finaliza la compra del carrito (para AJAX)
     */
    public function checkout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }
        
        if (!isset($_SESSION['correo'])) {
            $this->json(['success' => false, 'message' => 'Usuario no logueado.'], 401);
            return;
        }
        
        try {
            $correoUsuario = $_SESSION['correo'];
            
            // Verificar si el carrito tiene productos
            $cartItems = $this->cartService->getItems($correoUsuario);
            if (empty($cartItems)) {
                $this->json(['success' => false, 'message' => 'El carrito está vacío.'], 400);
                return;
            }
            
            // Crear la compra
            $result = $this->purchaseService->createPurchase($correoUsuario);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Compra realizada con éxito.']);
            } else {
                $this->json(['success' => false, 'message' => 'No se pudo procesar la compra.'], 400);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error al procesar la compra: ' . $e->getMessage()], 500);
        }
    }
}