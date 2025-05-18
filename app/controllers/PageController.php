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
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('PageController::index() - Iniciando método');
        }
        
        // Obtener productos destacados (en una implementación real, esto vendría del modelo)
        $featuredProducts = [
            [
                'id' => 1,
                'name' => 'Café Premium',
                'description' => 'Disfruta de nuestro café de la más alta calidad.',
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
                'name' => 'Blend Recién Llegado',
                'description' => 'Descubre nuestros productos más recientes.',
                'price' => 129.99,
                'image' => '/api/placeholder/400/400'
            ],
            [
                'id' => 4,
                'name' => 'Café Destacado',
                'description' => 'Uno de nuestros productos más populares.',
                'price' => 149.99,
                'image' => '/api/placeholder/400/400'
            ]
        ];
        
        // Datos para la vista
        $data = [
            'title' => 'Ethos Coffee - Inicio',
            'css' => ['index'], // Asegúrate de que este archivo exista
            'js' => ['main'],   // Asegúrate de que este archivo exista
            'featuredProducts' => $featuredProducts,
            'isLoggedIn' => isset($_SESSION['user_id']) // Cambiado de 'correo' a 'user_id' para consistencia
        ];
        
        // Tratar de renderizar con manejo de errores
        try {
            if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
                error_log('PageController::index() - Intentando renderizar vista "pages/home"');
            }
            
            return $this->render('pages/home', $data);
            
        } catch (\Exception $e) {
            // Log del error
            error_log('Error al renderizar la vista home: ' . $e->getMessage());
            
            // En modo debug, mostrar detalles del error
            if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
                echo '<h1>Error al renderizar la vista</h1>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
                exit;
            }
            
            // En producción, redirigir a error 500
            $this->response->setStatusCode(500);
            return 'Error interno del servidor al cargar la página de inicio.';
        }
    }
}