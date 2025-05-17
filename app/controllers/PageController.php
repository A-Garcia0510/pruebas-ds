<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/**
 * Controlador para las páginas estáticas
 */
class PageController extends BaseController
{
    /**
     * Muestra la página de inicio
     * 
     * @return string
     */
    public function index()
    {
        // Obtener productos destacados (en una implementación real, esto vendría del modelo)
        $featuredProducts = [
            [
                'id' => 1,
                'name' => 'Producto Premium',
                'description' => 'Disfruta de nuestros productos de la más alta calidad.',
                'price' => 99.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 2,
                'name' => 'Oferta Especial',
                'description' => 'Aprovecha nuestras ofertas por tiempo limitado.',
                'price' => 79.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 3,
                'name' => 'Recién Llegado',
                'description' => 'Descubre nuestros productos más recientes.',
                'price' => 129.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 4,
                'name' => 'Producto Destacado',
                'description' => 'Uno de nuestros productos más populares.',
                'price' => 149.99,
                'image' => '/api/placeholder/400/400'
            ]
        ];
        
        // Datos para la vista
        $data = [
            'title' => 'Ethos Coffe - Inicio',
            'css' => ['index'],
            'js' => ['main'],
            'featuredProducts' => $featuredProducts,
            'isLoggedIn' => isset($_SESSION['correo'])
        ];
        
        return $this->render('pages/home', $data);
    }
}