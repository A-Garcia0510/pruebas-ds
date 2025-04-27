<?php
// src/MVC/Controllers/Auth/LoginController.php
namespace App\MVC\Controllers\Auth;

use App\Auth\AuthFactory;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\MVC\Controllers\BaseController;

class LoginController extends BaseController
{
    private $authenticator;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Cargar configuración
        $config = require_once __DIR__ . '/../../../Config/Config.php';
        $dbConfig = new DatabaseConfiguration(
            $config['database']['host'],
            $config['database']['username'],
            $config['database']['password'],
            $config['database']['database']
        );
        
        // Crear conexión a la base de datos
        try {
            $database = new MySQLDatabase($dbConfig);
            
            // Crear el autenticador
            $this->authenticator = AuthFactory::createAuthenticator($database);
        } catch (\Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Muestra la vista de login
     */
    public function showLoginForm(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authenticator->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('Auth/login');
    }
    
    /**
     * Procesa el formulario de login
     */
    public function login(): void
    {
        // Si no es una petición POST, redirigir al formulario
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }
        
        $email = $_POST['correo'] ?? '';
        $password = $_POST['contra'] ?? '';
        
        // Intentar autenticar
        if ($this->authenticator->authenticate($email, $password)) {
            // Éxito - redirigir al dashboard
            $this->redirect('/dashboard');
        } else {
            // Falló la autenticación
            // En un sistema más avanzado, podrías usar mensajes flash para mostrar errores
            $_SESSION['error_message'] = 'Datos incorrectos. Por favor, inténtalo de nuevo.';
            $this->redirect('/login');
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void
    {
        if ($this->authenticator->logout()) {
            $this->redirect('/login');
        } else {
            $this->redirect('/dashboard');
        }
    }
}