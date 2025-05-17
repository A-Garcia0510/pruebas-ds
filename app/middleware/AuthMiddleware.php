<?php
namespace App\Middleware;

use App\Auth\AuthFactory;
use App\Core\Request;
use App\Core\Response;
use App\Core\App;

/**
 * Middleware para verificar que el usuario esté autenticado
 */
class AuthMiddleware extends Middleware
{
    /**
     * Rutas que son excluidas del middleware
     * 
     * @var array
     */
    private $excludedRoutes;
    
    /**
     * Constructor del middleware de autenticación
     * 
     * @param array $excludedRoutes Rutas excluidas del middleware (opcional)
     */
    public function __construct(array $excludedRoutes = [])
    {
        $this->excludedRoutes = $excludedRoutes;
    }
    
    /**
     * Maneja la solicitud verificando la autenticación
     * 
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, Response $response, callable $next)
    {
        $path = $request->getPath();
        
        // Si la ruta está excluida, continuar
        if (in_array($path, $this->excludedRoutes)) {
            return $next($request, $response);
        }
        
        // Obtener autenticador y verificar si el usuario está autenticado
        $authenticator = AuthFactory::createAuthenticator(App::$app->getDB());
        
        if (!$authenticator->isAuthenticated()) {
            $response->redirect('/login');
            return;
        }
        
        // Si está autenticado, continuar
        return $next($request, $response);
    }
}