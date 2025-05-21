<?php
namespace App\Controllers;

use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Core\Container;

/**
 * Controlador base que todos los controladores deben extender
 */
abstract class BaseController
{
    protected $request;
    protected $response;
    protected $config;
    protected $container;
    
    /**
     * Constructor del controlador base
     * 
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Container $container
     */
    public function __construct(
        RequestInterface $request, 
        ResponseInterface $response,
        Container $container
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
        
        try {
            $this->config = $container->get('config');
            if (!is_array($this->config)) {
                throw new \Exception('La configuración debe ser un array');
            }
        } catch (\Exception $e) {
            // Si no se puede obtener la configuración, usar un array vacío
            $this->config = [];
            error_log('Error al obtener la configuración: ' . $e->getMessage());
        }
        
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
        // No modificar URLs absolutas (comienzan con http:// o https://)
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            $this->response->redirect($url);
            return;
        }
            
        // Si la URL es relativa pero no comienza con /pruebas-ds/public, agregarla
        if ($url[0] === '/' && strpos($url, '/pruebas-ds/public') !== 0) {
            // Asegurarse que no hay doble barra
            if ($url !== '/') {
                $url = '/pruebas-ds/public' . $url;
            } else {
                $url = '/pruebas-ds/public/';
            }
        }
        
        // Si es una URL relativa sin barra inicial, agregarle la barra y el prefijo
        if ($url[0] !== '/') {
            $url = '/pruebas-ds/public/' . $url;
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