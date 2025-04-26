<?php
// src/MVC/Controllers/Shop/ProductController.php
namespace App\MVC\Controllers\Shop;

use App\MVC\Controllers\BaseController;
use App\Shop\Services\ProductService;
use App\Core\Database\DatabaseInterface;
use App\Auth\Services\Authenticator;

class ProductController extends BaseController
{
    private $productService;
    
    /**
     * Constructor
     * 
     * @param DatabaseInterface $db
     * @param Authenticator $auth
     * @param ProductService $productService
     */
    public function __construct(
        DatabaseInterface $db, 
        Authenticator $auth,
        ProductService $productService
    ) {
        parent::__construct($db, $auth);
        $this->productService = $productService;
    }
    
    /**
     * Muestra el listado de productos
     */
    public function index(): void
    {
        // Obtener parámetros de paginación
        $page = (int)$this->get('page', 1);
        $limit = (int)$this->get('limit', 12); // 12 productos por página
        
        // Obtener productos
        $products = $this->productService->getProducts($page, $limit);
        $totalProducts = $this->productService->countProducts();
        $totalPages = ceil($totalProducts / $limit);
        
        // Renderizar vista
        $this->render('Shop/products', [
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'layout' => 'main'
        ]);
    }
    
    /**
     * Muestra los detalles de un producto
     */
    public function details(): void
    {
        $productId = (int)$this->get('id', 0);
        
        if ($productId <= 0) {
            $this->redirect('/products');
            return;
        }
        
        $product = $this->productService->getProductById($productId);
        
        if (!$product) {
            // Si el producto no existe, redirigir a la lista
            $this->redirect('/products');
            return;
        }
        
        // Renderizar vista
        $this->render('Shop/product_detail', [
            'product' => $product,
            'layout' => 'main'
        ]);
    }
    
    /**
     * Buscar productos
     */
    public function search(): void
    {
        $query = $this->get('q', '');
        $page = (int)$this->get('page', 1);
        $limit = (int)$this->get('limit', 12);
        
        if (empty($query)) {
            $this->redirect('/products');
            return;
        }
        
        $products = $this->productService->searchProducts($query, $page, $limit);
        $totalProducts = $this->productService->countSearchResults($query);
        $totalPages = ceil($totalProducts / $limit);
        
        // Renderizar vista de búsqueda
        $this->render('Shop/search_results', [
            'products' => $products,
            'query' => $query,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'layout' => 'main'
        ]);
    }
}