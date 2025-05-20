<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Shop\Repositories\ProductRepository;

/**
 * Controlador para páginas estáticas
 */
class PageController extends BaseController
{
    protected $productRepository;
    
    /**
     * Constructor del controlador
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        
        // Inicializar el repositorio de productos
        $db = \App\Core\App::$app->db;
        $this->productRepository = new ProductRepository($db);
    }
    
    /**
     * Muestra la página de inicio
     */
    public function index()
    {
        // Obtener solo 3 productos destacados
        $featuredProducts = $this->productRepository->findAll(3);
        
        return $this->render('pages/home', [
            'title' => 'Inicio - Ethos Coffee',
            'description' => 'Bienvenido a Ethos Coffee',
            'featuredProducts' => $featuredProducts,
            'css' => ['index']
        ]);
    }
    
    /**
     * Muestra la página de servicios
     */
    public function services()
    {
        return $this->render('pages/services', [
            'title' => 'Servicios - Ethos Coffee',
            'description' => 'Descubre todos los servicios que ofrecemos para la comunidad universitaria',
            'css' => ['services']
        ]);
    }
    
    /**
     * Muestra la página de ayuda
     */
    public function help()
    {
        return $this->render('pages/help', [
            'title' => 'Ayuda - Ethos Coffee',
            'description' => '¿Necesitas ayuda? Estamos aquí para asistirte',
            'css' => ['help']
        ]);
    }
}