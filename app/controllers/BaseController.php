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
        
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('BaseController::__construct() - Controlador inicializado: ' . get_class($this));
        }
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
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('BaseController::render() - Renderizando vista: ' . $view);
        }
        
        // Añadir configuración a los datos disponibles en la vista
        $data['config'] = $this->config;
        
        // Extraer los datos para que estén disponibles en la vista
        extract($data);
        
        $viewPath = BASE_PATH . '/app/views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            $error = "La vista {$view} no existe en {$viewPath}";
            error_log($error);
            throw new \Exception($error);
        }
        
        // Iniciar buffer de salida para la vista
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        // Si no hay layout especificado o se ha establecido explícitamente a false, devolver solo el contenido
        if (isset($data['layout']) && $data['layout'] === false) {
            return $content;
        }
        
        // Por defecto usa el layout 'main'
        $layout = $data['layout'] ?? 'main';
        $layoutPath = BASE_PATH . '/app/views/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            $error = "El layout {$layout} no existe en {$layoutPath}";
            error_log($error);
            throw new \Exception($error);
        }
        
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('BaseController::render() - Usando layout: ' . $layout);
            error_log('BaseController::render() - Layout path: ' . $layoutPath);
        }
        
        // Renderizar con el layout
        ob_start();
        include $layoutPath;
        $fullContent = ob_get_clean();
        
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('BaseController::render() - Renderizado completo, tamaño: ' . strlen($fullContent) . ' bytes');
        }
        
        return $fullContent;
    }
    
    /**
     * Redirige a una URL específica
     * 
     * @param string $url URL a redireccionar
     */
    protected function redirect($url)
    {
        // Si es una URL relativa, agregarle la URL base
        if (isset($this->config['app']['url']) && !empty($this->config['app']['url']) && $url[0] === '/') {
            $url = $this->config['app']['url'] . $url;
        }
        
        // Log para depuración
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            error_log('BaseController::redirect() - Redirigiendo a: ' . $url);
        }
        
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