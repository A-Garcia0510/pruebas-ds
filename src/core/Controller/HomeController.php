<?php
/**
 * HomeController - Controlador para la página de inicio
 */
require_once __DIR__ . '/BaseController.php';

class HomeController extends BaseController {
    /**
     * Constructor de la clase HomeController
     */
    public function __construct() {
        // Inicializar el controlador
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Método principal que maneja la página de inicio
     */
    public function index() {
        // Preparar los datos para la vista
        $data = [
            'title' => 'Ethos Coffe - Inicio',
            'isLoggedIn' => $this->isLoggedIn(),
            'user' => $this->getLoggedInUser(),
            'featuredProducts' => $this->getFeaturedProducts()
        ];
        
        // Cargar la vista de inicio
        $this->view('home/index', $data);
    }
    
    /**
     * Obtiene los productos destacados para mostrar en la página de inicio
     * En una implementación real, esto podría venir de una base de datos
     * 
     * @return array Lista de productos destacados
     */
    private function getFeaturedProducts() {
        // En un sistema real, estos datos vendrían de un modelo
        return [
            [
                'id' => 1,
                'name' => 'Café Artesanal Premium',
                'description' => 'Disfruta de nuestro café de especialidad de la más alta calidad.',
                'price' => 99.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 2,
                'name' => 'Pack Degustación',
                'description' => 'Prueba nuestra selección de cafés de diferentes orígenes.',
                'price' => 79.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 3,
                'name' => 'Café Orgánico',
                'description' => 'Café cultivado respetando el medio ambiente, sin pesticidas.',
                'price' => 129.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 4,
                'name' => 'Accesorios para Café',
                'description' => 'Todo lo que necesitas para preparar el café perfecto en casa.',
                'price' => 149.99,
                'image' => '/api/placeholder/400/400'
            ]
        ];
    }
}