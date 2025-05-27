<?php
namespace App\Controllers;

use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Shop\Repositories\ProductRepository;
use App\Shop\Exceptions\ProductNotFoundException;
use App\Core\Container;
use App\Models\Review;

class ProductController extends BaseController
{
    protected $productRepository;
    protected $reviewModel;
    
    /**
     * Constructor del controlador de productos
     */
    public function __construct(
        RequestInterface $request, 
        ResponseInterface $response,
        ProductRepository $productRepository,
        Container $container,
        Review $reviewModel
    ) {
        parent::__construct($request, $response, $container);
        $this->productRepository = $productRepository;
        $this->reviewModel = $reviewModel;
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
            
            // Obtener reseñas del producto
            $reviews = $this->reviewModel->getByProduct($id);
            $averageRating = $this->reviewModel->getAverageRating($id);
            
            // Renderizar la vista de detalle de producto
            return $this->render('products/detail', [
                'product' => $product,
                'title' => $product->getName(),
                'isLoggedIn' => $isLoggedIn,
                'css' => ['detalleproducto'],
                'reviews' => $reviews,
                'averageRating' => $averageRating
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
        try {
            // Obtener parámetros de la solicitud
            $category = $_GET['category'] ?? null;
            
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
            $this->response->json([
                'success' => true,
                'products' => $productsArray
            ]);
        } catch (\Exception $e) {
            error_log("Error en ProductController::api: " . $e->getMessage());
            $this->response->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
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
            $commandInvoker = new \App\Shop\Commands\CartCommandInvoker($cartService);

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

    /**
     * API para agregar una reseña
     */
    public function addReview()
    {
        // Verificar si la solicitud es POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
        }

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['correo'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Debes iniciar sesión para dejar una reseña'
            ]);
        }

        try {
            // Obtener datos de la solicitud
            $data = $this->request->getBody();
            
            if (!$data) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Datos inválidos'
                ]);
            }

            // Validar datos requeridos
            if (!isset($data['producto_id']) && !isset($data['producto_ID'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Falta el ID del producto'
                ]);
            }

            // Normalizar los nombres de los campos
            $productoId = $data['producto_id'] ?? $data['producto_ID'];
            $contenido = $data['contenido'] ?? '';
            $calificacion = $data['calificacion'] ?? 0;

            if (empty($contenido) || empty($calificacion)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
            }

            // Crear la reseña
            $this->reviewModel->create(
                $productoId,
                $_SESSION['user_id'],
                $contenido,
                $calificacion
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Reseña agregada exitosamente'
            ]);

        } catch (\Exception $e) {
            error_log("Error en ProductController::addReview: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al agregar la reseña: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API para reportar una reseña
     */
    public function reportReview()
    {
        // Verificar si la solicitud es POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
        }

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Debes iniciar sesión para reportar una reseña'
            ]);
        }

        try {
            // Obtener datos de la solicitud
            $data = $this->request->getBody();
            
            if (!$data) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Datos inválidos'
                ]);
            }

            // Normalizar el ID de la reseña
            $reviewId = $data['review_ID'] ?? $data['review_id'] ?? null;
            $razon = $data['razon'] ?? '';

            // Validar datos requeridos
            if (empty($reviewId) || empty($razon)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
            }

            // Reportar la reseña
            $this->reviewModel->report(
                $reviewId,
                $_SESSION['user_id'],
                $razon
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Reseña reportada exitosamente'
            ]);

        } catch (\Exception $e) {
            error_log("Error en ProductController::reportReview: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Error al reportar la reseña: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina una reseña.
     * Requiere autenticación y que el usuario sea el autor de la reseña.
     * 
     * @return string JSON response
     */
    public function deleteReview()
    {
        // Verificar si la solicitud es AJAX y POST
        if (!$this->request->isAjax() || $this->request->getMethod() !== 'POST') {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Método no permitido.'
            ]);
        }

        // Verificar autenticación
        if (!isset($_SESSION['user_id'])) {
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'No autenticado.'
            ]);
        }

        try {
            $userId = $_SESSION['user_id'];
            $body = $this->request->getBody();
            
            // Normalizar el ID de la reseña
            $reviewId = $body['review_ID'] ?? $body['review_id'] ?? null;
            
            if (empty($reviewId)) {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => 'ID de reseña no proporcionado.'
                ]);
            }

            // Verificar si la reseña existe y pertenece al usuario
            $review = $this->reviewModel->findReviewById($reviewId);

            if (!$review) {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Reseña no encontrada.'
                ]);
            }

            if ($review['usuario_ID'] != $userId) {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => 'No tienes permiso para eliminar esta reseña.'
                ]);
            }

            // Intentar eliminar la reseña
            $deleted = $this->reviewModel->deleteReview($reviewId);

            if ($deleted) {
                return $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Reseña eliminada exitosamente.'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false, 
                    'message' => 'No se pudo eliminar la reseña.'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Error al eliminar reseña: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false, 
                'message' => 'Error al eliminar la reseña: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Método auxiliar para respuestas JSON
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}