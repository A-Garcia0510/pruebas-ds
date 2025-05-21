<?php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Middleware para verificar si el usuario está autenticado
 */
class AuthMiddleware extends Middleware
{
    /**
     * Rutas que están protegidas por este middleware
     * 
     * @var array
     */
    private $protectedRoutes;
    
    /**
     * Constructor del middleware
     * 
     * @param array $protectedRoutes Rutas que requieren autenticación
     */
    public function __construct($protectedRoutes = [])
    {
        $this->protectedRoutes = $protectedRoutes;
    }
    
    /**
     * Verifica si el usuario está autenticado antes de permitir el acceso
     * 
     * @param Request $request Objeto de solicitud
     * @param Response $response Objeto de respuesta
     * @param callable $next Siguiente middleware o controlador
     * @return mixed
     */
    public function execute(Request $request, Response $response, callable $next)
    {
        // Asegurarse de que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $path = $request->getPath();
        
        // Si la ruta actual está protegida y el usuario no está autenticado
        if (in_array($path, $this->protectedRoutes) && !isset($_SESSION['user_id'])) {
            // Guardar la URL solicitada para redirigir después del login (opcional)
            $_SESSION['redirect_after_login'] = $path;
            
            // Mensaje para informar al usuario
            $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página.';
            
            // Redirigir al login usando la ruta base correcta
            $response->redirect('/pruebas-ds/public/login');
            return;
        }
        
        // Si el usuario está autenticado o la ruta no está protegida, continuar
        return $next($request, $response);
    }
}