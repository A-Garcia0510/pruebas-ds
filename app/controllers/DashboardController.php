<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;

/**
 * Controlador para el dashboard del usuario
 */
class DashboardController extends BaseController
{
    private $authenticator;
    private $userRepository;
    
    /**
     * Constructor del controlador de dashboard
     * 
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        
        // Obtener el autenticador y repositorio de usuarios
        $database = \App\Core\App::$app->getDB();
        $this->authenticator = AuthFactory::createAuthenticator($database);
        $this->userRepository = AuthFactory::createUserRepository($database);
    }
    
    /**
     * Página principal del dashboard
     */
    public function index()
    {
        // Verificar autenticación (como medida adicional de seguridad)
        if (!$this->authenticator->isAuthenticated()) {
            $this->redirect('/login');
            return;
        }
        
        // Obtener correo del usuario actual
        $correo = $this->authenticator->getCurrentUserEmail();
        
        // Obtener los datos del usuario
        $user = $this->userRepository->findByEmail($correo);
        
        if (!$user) {
            return $this->render('errors/general', [
                'title' => 'Error',
                'message' => 'No se pudieron recuperar los datos del usuario.'
            ]);
        }
        
        // Preparar datos para la vista
        $data = [
            'title' => 'Mi Cuenta',
            'css' => ['dashboard'],
            'nombre' => $user->getNombre(),
            'apellidos' => $user->getApellidos(),
            'correo' => $correo
        ];
        
        return $this->render('dashboard/index', $data);
    }
}