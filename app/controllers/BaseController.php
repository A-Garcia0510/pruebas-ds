<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\App;

/**
 * Controlador base que todos los controladores deben extender
 */
abstract class BaseController
{
    protected $request;
    protected $response;
    protected $config;
    
    /**
     * Constructor del controlador base
     * 
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->config = App::$app->config;
    }
    
    /**
     * Renderiza una vista con los datos proporcionados
     * 
     * @param string $view Ruta a la vista (sin extensión .php)
     * @param array $data Datos que se pasan a la vista
     * @return string Contenido HTML renderizado
     */
    protected function render($view, $data = [])
    {
        // Añadir configuración a los datos disponibles en la vista
        $data['config'] = $this->config;
        
        // Extraer los datos para que estén disponibles en la vista
        extract($data);
        
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("La vista {$view} no existe");
        }
        
        // Iniciar buffer de salida
        ob_start();
        include_once $viewPath;
        $content = ob_get_clean();
        
        // Si no hay layout especificado, devolver solo el contenido
        if (!isset($data['layout']) || $data['layout'] === false) {
            return $content;
        }
        
        // Por defecto usa el layout 'main'
        $layout = $data['layout'] ?? 'main';
        $layoutPath = dirname(__DIR__) . '/views/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("El layout {$layout} no existe");
        }
        
        // Renderizar con el layout
        ob_start();
        include_once $layoutPath;
        return ob_get_clean();
    }
    
    /**
     * Redirige a una URL específica
     * 
     * @param string $url URL a redireccionar
     */
    protected function redirect($url)
    {
        $this->response->redirect($url);
    }
    
    /**
     * Devuelve una respuesta JSON
     * 
     * @param mixed $data Datos a convertir a JSON
     * @param int $statusCode Código de estado HTTP
     */
    protected function json($data, $statusCode = 200)
    {
        $this->response->setStatusCode($statusCode);
        $this->response->setContentType('application/json');
        echo json_encode($data);
    }
}