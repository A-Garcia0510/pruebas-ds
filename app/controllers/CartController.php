<?php
namespace App\Controllers;

use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Shop\Services\CartService;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Repositories\PurchaseRepository;
use App\Shop\Services\PurchaseService;
use App\Shop\Services\ProductService;
use App\Shop\Commands\CartCommandInvoker;
use App\Core\Container;
use App\Controllers\BaseController;

/**
 * Controlador para gestionar las operaciones del carrito de compras
 */
class CartController extends BaseController
{
    private CartService $cartService;
    private ProductRepository $productRepository;
    private PurchaseRepository $purchaseRepository;
    private PurchaseService $purchaseService;
    private ProductService $productService;
    private CartCommandInvoker $commandInvoker;
    
    /**
     * Constructor del controlador
     */
    public function __construct(
        CartService $cartService,
        RequestInterface $request,
        ResponseInterface $response,
        ProductRepository $productRepository,
        PurchaseRepository $purchaseRepository,
        PurchaseService $purchaseService,
        ProductService $productService,
        CartCommandInvoker $commandInvoker,
        Container $container
    ) {
        parent::__construct($request, $response, $container);
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseService = $purchaseService;
        $this->productService = $productService;
        $this->commandInvoker = $commandInvoker;
    }
    
    /**
     * Muestra la página del carrito
     */
    public function index()
    {
        if (!isset($_SESSION['correo'])) {
            $_SESSION['message'] = 'Debes iniciar sesión para acceder al carrito';
            $_SESSION['message_type'] = 'error';
            $this->response->redirect('/pruebas-ds/public/login');
            return;
        }
        
        return $this->render('cart/index', [
            'title' => 'Carrito de Compras - Ethos Coffee',
            'description' => 'Tu carrito de compras',
            'css' => ['cart']
        ]);
    }
    
    /**
     * Obtiene los productos del carrito (para AJAX)
     */
    public function getItems()
    {
        if (!isset($_SESSION['correo'])) {
            $this->response->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
            return;
        }
        
        try {
            $userEmail = $_SESSION['correo'];
            $items = $this->cartService->getItems($userEmail);
            $total = $this->cartService->getTotal($userEmail);
            
            $this->response->json([
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
            ]);
        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'message' => 'Error al obtener el carrito: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Agrega un producto al carrito (para AJAX)
     */
    public function addItem()
    {
        if (!isset($_SESSION['correo'])) {
            $this->response->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
            return;
        }
        
        try {
            $data = $this->request->getBody();
            
            if (!isset($data['producto_ID']) || !isset($data['cantidad'])) {
                $this->response->json([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ], 400);
                return;
            }
            
            // Crear y ejecutar el comando
            $command = new \App\Shop\Commands\AddToCartCommand(
                $this->cartService,
                $_SESSION['correo'],
                $data['producto_ID'],
                $data['cantidad'],
                $data['actualizar'] ?? false
            );
            
            if ($this->commandInvoker->executeCommand($command)) {
                $this->response->json([
                    'success' => true,
                    'message' => 'Producto agregado al carrito'
                ]);
            } else {
                $this->response->json([
                    'success' => false,
                    'message' => 'Error al agregar el producto al carrito'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en CartController::addItem: " . $e->getMessage());
            $this->response->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Elimina un producto del carrito (para AJAX)
     */
    public function removeItem()
    {
        if (!isset($_SESSION['correo'])) {
            $this->response->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
            return;
        }
        
        try {
            $data = $this->request->getBody();
            
            if (!isset($data['producto_ID'])) {
                $this->response->json([
                    'success' => false,
                    'message' => 'ID de producto no válido'
                ], 400);
                return;
            }

            // Obtener la cantidad actual del producto antes de eliminarlo
            $cartItem = $this->cartService->getCartItem($_SESSION['correo'], $data['producto_ID']);
            $quantity = $cartItem ? $cartItem['cantidad'] : 0;
            
            // Crear y ejecutar el comando
            $command = new \App\Shop\Commands\RemoveFromCartCommand(
                $this->cartService,
                $_SESSION['correo'],
                $data['producto_ID'],
                $quantity
            );
            
            if ($this->commandInvoker->executeCommand($command)) {
                $this->response->json([
                    'success' => true,
                    'message' => 'Producto eliminado del carrito'
                ]);
            } else {
                $this->response->json([
                    'success' => false,
                    'message' => 'Error al eliminar el producto del carrito'
                ], 500);
            }
        } catch (\Exception $e) {
            error_log("Error en CartController::removeItem: " . $e->getMessage());
            $this->response->json([
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
        if (!isset($_SESSION['correo'])) {
            $this->response->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
            return;
        }
        
        try {
            $userEmail = $_SESSION['correo'];
            $cartItems = $this->cartService->getItems($userEmail);
            
            if (empty($cartItems)) {
                $this->response->json([
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ], 400);
                return;
            }
            
            $result = $this->purchaseService->createPurchase($userEmail);
            
            if ($result) {
                $this->response->json([
                    'success' => true,
                    'message' => 'Compra realizada con éxito'
                ]);
            } else {
                $this->response->json([
                    'success' => false,
                    'message' => 'No se pudo procesar la compra'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'message' => 'Error al procesar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function undoLastAction()
    {
        if (!isset($_SESSION['correo'])) {
            $this->response->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
            return;
        }
        
        try {
            if ($this->commandInvoker->undoLastCommand()) {
                $this->response->json([
                    'success' => true,
                    'message' => 'Última acción deshecha'
                ]);
            } else {
                $this->response->json([
                    'success' => false,
                    'message' => 'No hay acciones para deshacer'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'message' => 'Error al deshacer la acción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function redoLastAction()
    {
        if (!isset($_SESSION['correo'])) {
            $this->response->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
            return;
        }
        
        try {
            if ($this->commandInvoker->redoLastCommand()) {
                $this->response->json([
                    'success' => true,
                    'message' => 'Última acción rehecha'
                ]);
            } else {
                $this->response->json([
                    'success' => false,
                    'message' => 'No hay acciones para rehacer'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->response->json([
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