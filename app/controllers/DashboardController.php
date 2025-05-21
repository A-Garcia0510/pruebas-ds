<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;
use App\Core\Container;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseInterface;

/**
 * Controlador para la sección de Dashboard del usuario
 */
class DashboardController extends BaseController
{
    private $database;
    private $authenticator;
    private $userRepository;

    public function __construct(
        Request $request,
        Response $response,
        Container $container,
        DatabaseInterface $database
    ) {
        parent::__construct($request, $response, $container);
        $this->database = $database;
        $this->authenticator = AuthFactory::createAuthenticator($database);
        $this->userRepository = AuthFactory::createUserRepository($database);
    }

    /**
     * Muestra el dashboard del usuario
     * 
     * @return string
     */
    public function index()
    {
        try {
            // Verificar si el usuario está autenticado
            if (!$this->authenticator->isAuthenticated()) {
                // Almacenar mensaje de error en sesión
                $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página.';
                return $this->redirect('/login');
            }
            
            // Obtener email del usuario actual
            $correo = $this->authenticator->getCurrentUserEmail();
            
            // Obtener los datos del usuario
            $user = $this->userRepository->findByEmail($correo);
            
            if (!$user) {
                throw new \Exception("Error: No se pudieron recuperar los datos del usuario.");
            }
            
            $data = [
                'title' => 'Mi Cuenta - Café Aroma',
                'user' => $user,
                'css' => ['dashboard'],
                'layout' => 'main'
            ];
            
            return $this->render('dashboard/index', $data);
            
        } catch (\Exception $e) {
            // Log del error
            error_log('Error en DashboardController: ' . $e->getMessage());
            $_SESSION['error'] = 'Error en el sistema: ' . $e->getMessage();
            return $this->redirect('/');
        }
    }
}