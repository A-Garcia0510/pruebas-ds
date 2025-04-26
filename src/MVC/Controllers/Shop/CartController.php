<?php
// src/MVC/Controllers/Shop/CartController.php
namespace App\MVC\Controllers\Shop;

use App\MVC\Controllers\BaseController;
use App\Shop\Services\CartService;
use App\Shop\Services\ProductService;
use App\Core\Database\DatabaseInterface;
use App\Auth\Services\Authenticator;

class CartController extends BaseController
{
    private $cartService;
    private $productService;
    
    /**
     * Constructor
     * 
     * @param DatabaseInterface $db
     * @param Authenticator $auth
     * @param CartService $cartService
     * @param ProductService $productService
     */
    public function __construct(
        DatabaseInterface $db, 
        Authenticator $auth,
        CartService $cartService,
        ProductService $productService
    ) {
        parent::__construct($db, $auth);
        $this->cartService = $cartService;
        $this->productService = $productService;
    }
    
    /**
     * Muestra el carrito de compras
     */
    public function show(): void
    {
        $cartItems = $this->cartService->getCartItems();
        $total = $this->cartService->getCartTotal();
        
        // Renderizar vista
        $this->render('Shop/cart', [
            'cartItems' => $cartItems,
            'total' => $total,
            'layout' => 'main'
        ]);
    }
    
    /**
     * Añade un producto al carrito
     */
    public function add(): void
    {
        $productId = (int)$this->post('producto_id', 0);
        $quantity = (int)$this->post('cantidad', 1);
        
        if ($productId <= 0 || $quantity <= 0) {
            // Manejar error de parámetros
            $this->redirect('/products');
            return;
        }
        
        // Validar existencia del producto
        $product = $this->productService->getProductById($productId);
        if (!$product) {
            // Producto no existe
            $this->redirect('/products');
            return;
        }
        
        // Añadir al carrito
        $result = $this->cartService->addToCart($productId, $quantity);
        
        if ($result) {
            // Redirigir al carrito con mensaje de éxito
            $_SESSION['cart_message'] = 'Producto añadido al carrito';
        } else {
            // Error al añadir (tal vez stock insuficiente)
            $_SESSION['cart_error'] = 'No se pudo añadir el producto al carrito';
        }
        
        // Si es una petición AJAX, responder apropiadamente
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Producto añadido al carrito' : 'No se pudo añadir el producto',
                'cartCount' => $this->cartService->getCartItemCount()
            ]);
            exit;
        }
        
        // Si no es AJAX, redirigir al carrito
        $this->redirect('/cart');
    }
    
    /**
     * Elimina un producto del carrito
     */
    public function remove(): void
    {
        $productId = (int)$this->post('producto_id', 0);
        
        if ($productId <= 0) {
            $this->redirect('/cart');
            return;
        }
        
        $this->cartService->removeFromCart($productId);
        
        // Si es una petición AJAX, responder apropiadamente
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'cartCount' => $this->cartService->getCartItemCount(),
                'cartTotal' => $this->cartService->getCartTotal()
            ]);
            exit;
        }
        
        // Si no es AJAX, redirigir al carrito
        $this->redirect('/cart');
    }
    
    /**
     * Procesa el checkout
     */
    public function checkout(): void
    {
        // Verificar autenticación
        if (!$this->checkAuth()) {
            return;
        }
        
        // Verificar que hay productos en el carrito
        if ($this->cartService->isEmpty()) {
            $this->redirect('/cart');
            return;
        }
        
        // Mostrar página de checkout (confirmación)
        $this->render('Shop/checkout', [
            'cartItems' => $this->cartService->getCartItems(),
            'total' => $this->cartService->getCartTotal(),
            'layout' => 'main'
        ]);
    }
    
    /**
     * Procesa el pago y finaliza la compra
     */
    public function process(): void
    {
        // Verificar autenticación
        if (!$this->checkAuth()) {
            return;
        }
        
        // Verificar que hay productos en el carrito
        if ($this->cartService->isEmpty()) {
            $this->redirect('/cart');
            return;
        }
        
        // Aquí iría la lógica de procesamiento de pago
        // Por ahora, simplemente redirigimos a confirmación
        
        $this->redirect('/checkout/confirm');
    }
    
    /**
     * Muestra la confirmación de compra
     */
    public function confirm(): void
    {
        // Verificar autenticación
        if (!$this->checkAuth()) {
            return;
        }
        
        // Renderizar vista
        $this->render('Shop/confirmation', [
            'layout' => 'main'
        ]);
    }
}