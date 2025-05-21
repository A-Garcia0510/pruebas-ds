<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Exceptions\ProductNotFoundException;

class ProductController extends BaseController
{
    protected $productRepository;
    
    /**
     * Constructor del controlador de productos
     * 
     * @param Request $request
     * @param Response $response
     * @param ProductRepository $productRepository
     */
    public function __construct(Request $request, Response $response, ProductRepository $productRepository = null)
    {
        parent::__construct($request, $response);
        $this->productRepository = $productRepository;
        
        // Si no se inyecta el repositorio, lo creamos
        if ($this->productRepository === null) {
            // Obtener la instancia de base de datos del contenedor
            $db = \App\Core\App::$app->db;
            $this->productRepository = new ProductRepository($db);
        }
    }
    
    /**
     * Muestra el listado de productos
     */
    public function index()
    {
        // Obtener todas las categorías
        $categories = $this->productRepository->getAllCategories();
        
        // Obtener todos los productos
        $products = $this->productRepository->findAll();
        
        // Verificar si el usuario está autenticado
        $isLoggedIn = isset($_SESSION['correo']);
        
        // Renderizar la vista de listado de productos
        return $this->render('products/index', [
            'categories' => $categories,
            'products' => $products,
            'title' => 'Nuestros Productos',
            'isLoggedIn' => $isLoggedIn,
            'css' => ['productos']
        ]);
    }
    
    /**
     * Muestra los detalles de un producto específico
     * 
     * @param int $id ID del producto
     */
    public function detail($id = null)
    {
        // Si no se proporciona un ID, redirigir al listado
        if ($id === null) {
            $this->redirect('products');
            return;
        }
        
        // Convertir a entero
        $id = (int)$id;
        
        try {
            // Obtener el producto
            $product = $this->productRepository->findById($id);
            
            // Si el producto no existe, lanzar excepción
            if (!$product) {
                throw new ProductNotFoundException("Producto con ID $id no encontrado");
            }
            
            // Verificar si el usuario está autenticado
            $isLoggedIn = isset($_SESSION['correo']);
            
            // Renderizar la vista de detalle de producto
            return $this->render('products/detail', [
                'product' => $product,
                'title' => $product->getName(),
                'isLoggedIn' => $isLoggedIn,
                'css' => ['detalleproducto']
            ]);
            
        } catch (ProductNotFoundException $e) {
            // Manejar el error si el producto no existe
            $this->response->setStatusCode(404);
            return $this->render('errors/404', [
                'message' => $e->getMessage(),
                'title' => 'Producto no encontrado'
            ]);
        }
    }
    
    /**
     * Busca productos por categoría
     * 
     * @param string $category Categoría a buscar
     */
    public function byCategory($category = null)
    {
        // Si no se proporciona una categoría, redirigir al listado
        if ($category === null) {
            $this->redirect('products');
            return;
        }
        
        // Obtener todas las categorías
        $categories = $this->productRepository->getAllCategories();
        
        // Obtener productos por categoría
        $products = $this->productRepository->findByCategory($category);
        
        // Verificar si el usuario está autenticado
        $isLoggedIn = isset($_SESSION['correo']);
        
        // Renderizar la vista de listado de productos filtrados por categoría
        return $this->render('products/index', [
            'categories' => $categories,
            'products' => $products,
            'currentCategory' => $category,
            'title' => 'Productos - ' . $category,
            'isLoggedIn' => $isLoggedIn
        ]);
    }
    
    /**
     * API para obtener productos (JSON)
     */
    public function api()
    {
        // Obtener parámetros de la solicitud
        $category = $this->request->get('category');
        
        // Obtener productos según el filtro
        $products = [];
        if ($category && $category !== 'todos') {
            $products = $this->productRepository->findByCategory($category);
        } else {
            $products = $this->productRepository->findAll();
        }
        
        // Convertir objetos Product a arrays para JSON
        $productsArray = [];
        foreach ($products as $product) {
            $productsArray[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'category' => $product->getCategory()
            ];
        }
        
        // Devolver respuesta JSON
        return $this->json([
            'success' => true,
            'products' => $productsArray
        ]);
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
        
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['correo'])) {
            return $this->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para agregar productos al carrito'
            ], 401);
        }
        
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents('php://input'), true);
            $productId = $data['producto_ID'] ?? null;
            $quantity = $data['cantidad'] ?? 1;
            
            // Validar datos
            if (!$productId || !is_numeric($productId) || !is_numeric($quantity) || $quantity < 1) {
                return $this->json([
                    'success' => false,
                    'message' => 'Datos inválidos'
                ], 400);
            }

            // Inicializar servicios necesarios
            $dbConfig = new \App\Core\Database\DatabaseConfiguration(
                $this->config['database']['host'],
                $this->config['database']['username'],
                $this->config['database']['password'],
                $this->config['database']['database']
            );
            $db = new \App\Core\Database\MySQLDatabase($dbConfig);
            $cartService = new \App\Shop\Services\CartService($db, $this->productRepository);
            $commandInvoker = new \App\Shop\Commands\CartCommandInvoker();

            // Crear y ejecutar el comando
            $command = new \App\Shop\Commands\AddToCartCommand(
                $cartService,
                $_SESSION['correo'],
                (int)$productId,
                (int)$quantity
            );

            if ($commandInvoker->executeCommand($command)) {
                return $this->json([
                    'success' => true,
                    'message' => 'Producto agregado al carrito'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Error al agregar el producto al carrito'
                ], 500);
            }
            
        } catch (\Exception $e) {
            error_log("Error en ProductController::addToCart: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}