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
        
        // Renderizar la vista de listado de productos
        return $this->render('products/index', [
            'categories' => $categories,
            'products' => $products,
            'title' => 'Nuestros Productos'
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
            
            // Debug para verificar que se está obteniendo el producto
            if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
                error_log('ProductController::detail() - Producto encontrado: ' . print_r($product, true));
            }
            
            // Renderizar la vista de detalle de producto
            return $this->render('products/detail', [
                'product' => $product,
                'title' => $product->getName()
            ]);
            
        } catch (ProductNotFoundException $e) {
            // Log del error
            error_log('ProductController::detail() - Error: ' . $e->getMessage());
            
            // Manejar el error si el producto no existe
            $this->response->setStatusCode(404);
            return $this->render('errors/404', [
                'message' => $e->getMessage(),
                'title' => 'Producto no encontrado'
            ]);
        } catch (\Exception $e) {
            // Log del error
            error_log('ProductController::detail() - Error inesperado: ' . $e->getMessage());
            
            // Manejar otros errores
            $this->response->setStatusCode(500);
            return $this->render('errors/500', [
                'message' => 'Ha ocurrido un error inesperado',
                'title' => 'Error interno'
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
        
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('ProductController::byCategory() - Buscando productos por categoría: ' . $category);
        }
        
        // Obtener todas las categorías
        $categories = $this->productRepository->getAllCategories();
        
        // Obtener productos por categoría
        $products = $this->productRepository->findByCategory($category);
        
        // Renderizar la vista de listado de productos filtrados por categoría
        return $this->render('products/index', [
            'categories' => $categories,
            'products' => $products,
            'currentCategory' => $category,
            'title' => 'Productos - ' . $category
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
        
        try {
            // Obtener producto
            $product = $this->productRepository->findById((int)$productId);
            
            // Verificar si el producto existe
            if (!$product) {
                throw new ProductNotFoundException("Producto no encontrado");
            }
            
            // Verificar stock
            if (!$product->hasStock($quantity)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Stock insuficiente. Solo hay ' . $product->getStock() . ' unidades disponibles.'
                ], 400);
            }
            
            // Inicializar carrito si no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            
            // Agregar al carrito o actualizar cantidad
            $found = false;
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['producto_ID'] == $productId) {
                    $item['cantidad'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['carrito'][] = [
                    'producto_ID' => $productId,
                    'nombre' => $product->getName(),
                    'precio' => $product->getPrice(),
                    'cantidad' => $quantity
                ];
            }
            
            // Devolver respuesta exitosa
            return $this->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cartCount' => count($_SESSION['carrito'])
            ]);
            
        } catch (ProductNotFoundException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}