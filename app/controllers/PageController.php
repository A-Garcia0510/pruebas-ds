<?php
namespace App\Controllers;

use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Shop\Repositories\ProductRepository;
use App\Core\Container;

/**
 * Controlador para páginas estáticas
 */
class PageController extends BaseController
{
    protected $productRepository;
    
    /**
     * Constructor del controlador
     */
    public function __construct(
        RequestInterface $request, 
        ResponseInterface $response,
        ProductRepository $productRepository,
        Container $container
    ) {
        parent::__construct($request, $response, $container);
        $this->productRepository = $productRepository;
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