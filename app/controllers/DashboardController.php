<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;

/**
 * Controlador para la sección de Dashboard del usuario
 */
class DashboardController extends BaseController
{
    /**
     * Muestra el dashboard del usuario
     * 
     * @return string
     */
    public function index()
    {
        // Configurar la base de datos
        $dbConfig = new DatabaseConfiguration(
            $this->config['database']['host'],
            $this->config['database']['username'],
            $this->config['database']['password'],
            $this->config['database']['database']
        );
        
        try {
            // Crear conexión a la base de datos
            $database = new MySQLDatabase($dbConfig);
            
            // Crear el autenticador
            $authenticator = AuthFactory::createAuthenticator($database);
            
            // Verificar si el usuario está autenticado
            if (!$authenticator->isAuthenticated()) {
                // Almacenar mensaje de error en sesión
                $_SESSION['error'] = 'Debes iniciar sesión para acceder a esta página.';
                return $this->redirect('/login');
            }
            
            // Obtener email del usuario actual
            $correo = $authenticator->getCurrentUserEmail();
            
            // Obtener los datos del usuario
            $userRepository = AuthFactory::createUserRepository($database);
            $user = $userRepository->findByEmail($correo);
            
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