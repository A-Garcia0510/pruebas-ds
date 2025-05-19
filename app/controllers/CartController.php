<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Shop\Services\CartService;
use App\Shop\Interfaces\ProductRepositoryInterface;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Shop\Exceptions\InsufficientStockException;
use App\Core\Database\DatabaseInterface;

class CartController extends BaseController
{
    protected $cartService;
    protected $productRepository;
    
    /**
     * Constructor del controlador del carrito
     * 
     * @param Request $request
     * @param Response $response
     * @param CartService $cartService
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Request $request, 
        Response $response, 
        CartService $cartService = null,
        ProductRepositoryInterface $productRepository = null
    ) {
        parent::__construct($request, $response);
        
        // Si no se inyectan las dependencias, las creamos
        if ($cartService === null || $productRepository === null) {
            // Obtener la instancia de base de datos del contenedor
            $db = \App\Core\App::$app->db;
            
            if ($productRepository === null) {
                $this->productRepository = new \App\Shop\Repositories\ProductRepository($db);
            } else {
                $this->productRepository = $productRepository;
            }
            
            if ($cartService === null) {
                $this->cartService = new CartService($db, $this->productRepository);
            } else {
                $this->cartService = $cartService;
            }
        } else {
            $this->cartService = $cartService;
            $this->productRepository = $productRepository;
        }
    }
    
    /**
     * Muestra el contenido del carrito
     */
    public function index()
    {
        try {
            // Obtener el ID del usuario de la sesión
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                // Si no hay usuario, redirigir al login
                $this->redirect('login');
                return;
            }
            
            // Obtener los items del carrito
            $cartItems = $this->cartService->getItems($userId);
            
            // Calcular el total del carrito
            $cartTotal = $this->cartService->getTotal($userId);
            
            // Renderizar la vista con los datos
            return $this->render('cart/index', [
                'cartItems' => $cartItems,
                'cartTotal' => $cartTotal,
                'title' => 'Carrito de Compras'
            ]);
            
        } catch (\Exception $e) {
            // Log del error
            error_log('CartController::index() - Error: ' . $e->getMessage());
            
            // Manejar el error
            $this->response->setStatusCode(500);
            return $this->render('errors/500', [
                'message' => 'Ha ocurrido un error al cargar el carrito: ' . $e->getMessage(),
                'title' => 'Error interno'
            ]);
        }
    }
    
    /**
     * API para agregar un producto al carrito
     */
    public function addToCart()
    {
        // Verificar si la solicitud es POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->json([
                'success' => false,
                'message' => 'Método no permitido'
            ], 405);
        }
        
        // Obtener datos del body
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['producto_ID'] ?? null;
        $quantity = $data['cantidad'] ?? 1;
        
        // Validar datos
        if (!$productId || !is_numeric($productId) || !is_numeric($quantity)) {
            return $this->json([
                'success' => false,
                'message' => 'Datos inválidos'
            ], 400);
        }
        
        try {
            // Obtener el ID del usuario de la sesión
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            // Agregar al carrito
            $this->cartService->addItem($userId, (int)$productId, (int)$quantity);
            
            // Contar items en el carrito para actualizar el contador
            $cartItems = $this->cartService->getItems($userId);
            
            return $this->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cartCount' => count($cartItems)
            ]);
            
        } catch (ProductNotFoundException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (InsufficientStockException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            error_log('CartController::addToCart() - Error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API para eliminar un producto del carrito
     */
    public function removeFromCart()
    {
        // Verificar si la solicitud es POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->json([
                'success' => false,
                'message' => 'Método no permitido'
            ], 405);
        }
        
        // Obtener datos del body
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['producto_ID'] ?? null;
        
        // Validar datos
        if (!$productId || !is_numeric($productId)) {
            return $this->json([
                'success' => false,
                'message' => 'ID de producto inválido'
            ], 400);
        }
        
        try {
            // Obtener el ID del usuario de la sesión
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            // Eliminar del carrito
            $this->cartService->removeItem($userId, (int)$productId);
            
            return $this->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito'
            ]);
            
        } catch (\Exception $e) {
            error_log('CartController::removeFromCart() - Error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API para vaciar el carrito
     */
    public function clearCart()
    {
        // Verificar si la solicitud es POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->json([
                'success' => false,
                'message' => 'Método no permitido'
            ], 405);
        }
        
        try {
            // Obtener el ID del usuario de la sesión
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            // Vaciar el carrito
            $this->cartService->clear($userId);
            
            return $this->json([
                'success' => true,
                'message' => 'Carrito vaciado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            error_log('CartController::clearCart() - Error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}