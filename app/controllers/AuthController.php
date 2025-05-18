<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;
use App\Auth\Models\User;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;

/**
 * Controlador para la autenticación de usuarios
 */
class AuthController extends BaseController
{
    /**
     * Muestra la página de inicio de sesión
     * 
     * @return string
     */
    public function login()
    {
        // Si el usuario ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            return $this->redirect('/dashboard');
        }

        $data = [
            'title' => 'Iniciar Sesión - Café-VT',
            'css' => ['login'],
            'js' => ['auth']
        ];

        return $this->render('auth/login', $data);
    }

    /**
     * Procesa el inicio de sesión
     * 
     * @return void
     */
    public function authenticate()
    {
        if (!$this->request->isPost()) {
            return $this->redirect('/login');
        }

        $email = $this->request->get('correo');
        $password = $this->request->get('contra');

        // Validación básica
        if (empty($email) || empty($password)) {
            // Almacenar mensaje de error en sesión para mostrarlo en la vista
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            return $this->redirect('/login');
        }

        try {
            // Configurar la base de datos
            $dbConfig = new DatabaseConfiguration(
                $this->config['database']['host'],
                $this->config['database']['username'],
                $this->config['database']['password'],
                $this->config['database']['database']
            );
            $database = new MySQLDatabase($dbConfig);
            
            // Crear el autenticador
            $authenticator = AuthFactory::createAuthenticator($database);
            
            if ($authenticator->authenticate($email, $password)) {
                // Redirigir al dashboard
                return $this->redirect('/dashboard');
            } else {
                $_SESSION['error'] = 'Datos incorrectos. Por favor, inténtalo de nuevo.';
                return $this->redirect('/login');
            }
        } catch (\Exception $e) {
            // Log del error
            error_log('Error de autenticación: ' . $e->getMessage());
            $_SESSION['error'] = 'Error en el sistema. Por favor, inténtalo más tarde.';
            return $this->redirect('/login');
        }
    }

    /**
     * Muestra la página de registro
     * 
     * @return string
     */
    public function register()
    {
        // Si el usuario ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            return $this->redirect('/dashboard');
        }

        $data = [
            'title' => 'Registro - Café-VT',
            'css' => ['registro'],
            'js' => ['auth']
        ];

        return $this->render('auth/register', $data);
    }

    /**
     * Procesa el registro de un nuevo usuario
     * 
     * @return void
     */
    public function store()
    {
        if (!$this->request->isPost()) {
            return $this->redirect('/register');
        }

        $nombre = $this->request->get('nombre');
        $apellidos = $this->request->get('apellidos');
        $email = $this->request->get('correo');
        $password = $this->request->get('contra');

        // Validación básica
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            return $this->redirect('/register');
        }

        try {
            // Configurar la base de datos
            $dbConfig = new DatabaseConfiguration(
                $this->config['database']['host'],
                $this->config['database']['username'],
                $this->config['database']['password'],
                $this->config['database']['database']
            );
            $database = new MySQLDatabase($dbConfig);
            
            // Crear el repositorio de usuarios
            $userRepository = AuthFactory::createUserRepository($database);
            
            // Verificar si el email ya existe
            if ($userRepository->emailExists($email)) {
                $_SESSION['error'] = 'El correo electrónico ya está registrado.';
                return $this->redirect('/register');
            }
            
            // Crear nuevo usuario
            $user = new User(null, $nombre, $apellidos, $email, $password);
            
            // Guardar usuario
            if ($userRepository->save($user)) {
                // Crear autenticador y autenticar al usuario
                $authenticator = AuthFactory::createAuthenticator($database);
                $authenticator->authenticate($email, $password);
                
                return $this->redirect('/dashboard');
            } else {
                $_SESSION['error'] = 'Error al crear el usuario. Por favor, inténtalo de nuevo.';
                return $this->redirect('/register');
            }
        } catch (\Exception $e) {
            // Log del error
            error_log('Error de registro: ' . $e->getMessage());
            $_SESSION['error'] = 'Error en el sistema. Por favor, inténtalo más tarde.';
            return $this->redirect('/register');
        }
    }

    /**
     * Cierra la sesión del usuario
     * 
     * @return void
     */
    public function logout()
    {
        // Destruir sesión
        session_start();
        session_unset();
        session_destroy();
        
        // Redirigir a la página de inicio
        return $this->redirect('/');
    }
}