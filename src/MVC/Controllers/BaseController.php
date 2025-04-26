<?php
// src/MVC/Controllers/BaseController.php
namespace App\MVC\Controllers;

use App\Auth\Services\Authenticator;
use App\Core\Database\DatabaseInterface;

abstract class BaseController
{
    protected $db;
    protected $auth;
    
    /**
     * Constructor base para controladores
     * 
     * @param DatabaseInterface $db
     * @param Authenticator|null $auth
     */
    public function __construct(DatabaseInterface $db, ?Authenticator $auth = null)
    {
        $this->db = $db;
        $this->auth = $auth;
    }
    
    /**
     * Renderiza una vista con datos
     * 
     * @param string $view Ruta de la vista (sin la extensión .php)
     * @param array $data Datos a pasar a la vista
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        // Extraer los datos para hacerlos accesibles como variables en la vista
        extract($data);
        
        // Construir la ruta completa a la vista
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        // Verificar que la vista existe
        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: $view");
        }
        
        // Iniciar el buffer de salida
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        // Si hay un layout definido, usarlo
        if (isset($layout)) {
            $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                echo $content;
            }
        } else {
            // Si no hay layout, mostrar el contenido directamente
            echo $content;
        }
    }
    
    /**
     * Redirige a una URL específica
     * 
     * @param string $url
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
    
    /**
     * Verifica si el usuario está autenticado y redirige si es necesario
     * 
     * @param bool $redirect Si true, redirige al login cuando no está autenticado
     * @return bool
     */
    protected function checkAuth(bool $redirect = true): bool
    {
        if (!$this->auth || !$this->auth->isAuthenticated()) {
            if ($redirect) {
                $this->redirect('/login');
            }
            return false;
        }
        return true;
    }
    
    /**
     * Obtiene los datos enviados por POST
     * 
     * @param string|null $key Si se proporciona, devuelve solo ese valor
     * @param mixed $default Valor por defecto si no existe la clave
     * @return mixed
     */
    protected function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obtiene los datos enviados por GET
     * 
     * @param string|null $key Si se proporciona, devuelve solo ese valor
     * @param mixed $default Valor por defecto si no existe la clave
     * @return mixed
     */
    protected function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        
        return $_GET[$key] ?? $default;
    }
}