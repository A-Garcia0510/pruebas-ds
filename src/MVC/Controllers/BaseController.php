<?php
// src/MVC/Controllers/BaseController.php
namespace App\MVC\Controllers;

class BaseController
{
    /**
     * Renderiza una vista con datos
     * 
     * @param string $view Ruta a la vista (relativa a la carpeta Views)
     * @param array $data Datos para pasar a la vista
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        // Extraer los datos para que estén disponibles como variables en la vista
        extract($data);
        
        // Ruta base para las vistas
        $viewsBasePath = __DIR__ . '/../../MVC/Views/';
        $viewPath = $viewsBasePath . $view . '.php';
        
        // Verificar si la vista existe
        if (!file_exists($viewPath)) {
            throw new \Exception("La vista {$view} no existe");
        }
        
        // Iniciar el buffer de salida
        ob_start();
        
        // Incluir la vista
        include $viewPath;
        
        // Obtener el contenido y limpiarlo
        $content = ob_get_clean();
        
        // Mostrar el contenido
        echo $content;
    }
    
    /**
     * Redirige a una URL
     * 
     * @param string $url URL a la que redirigir
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit();
    }
    
    /**
     * Devuelve una respuesta JSON
     * 
     * @param mixed $data Datos a convertir a JSON
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}