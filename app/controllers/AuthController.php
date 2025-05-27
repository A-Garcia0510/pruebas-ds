<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;
use App\Auth\Models\User;
use App\Core\Container;
use App\Core\Database\DatabaseInterface;

/**
 * Controlador para la autenticación de usuarios
 */
class AuthController extends BaseController
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
     * Muestra la página de inicio de sesión
     * 
     * @return string
     */
    public function login()
    {
        // Si el usuario ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            return $this->redirect('/pruebas-ds/public/dashboard');
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
            return $this->redirect('/pruebas-ds/public/login');
        }

        $email = $this->request->get('correo');
        $password = $this->request->get('contra');

        // Validación básica
        if (empty($email) || empty($password)) {
            // Almacenar mensaje de error en sesión para mostrarlo en la vista
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            return $this->redirect('/pruebas-ds/public/login');
        }

        try {
            if ($this->authenticator->authenticate($email, $password)) {
                // Obtener el ID del usuario
                $user = $this->userRepository->findByEmail($email);
                if (!$user) {
                    throw new \Exception("Error: No se pudo obtener la información del usuario.");
                }

                // Guardar datos en la sesión
                $_SESSION['correo'] = $email;
                $_SESSION['user_id'] = $user->getId();
                
                // Log para depuración
                error_log('Usuario autenticado - ID: ' . $user->getId() . ', Correo: ' . $email);
                
                // Redirigir al dashboard
                return $this->redirect('/pruebas-ds/public/dashboard');
            }
            else {
                $_SESSION['error'] = 'Datos incorrectos. Por favor, inténtalo de nuevo.';
                return $this->redirect('/pruebas-ds/public/login');
            }
        } catch (\Exception $e) {
            // Log del error
            error_log('Error de autenticación: ' . $e->getMessage());
            $_SESSION['error'] = 'Error en el sistema. Por favor, inténtalo más tarde.';
            return $this->redirect('/pruebas-ds/public/login');
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
            return $this->redirect('/pruebas-ds/public/dashboard');
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
            return $this->redirect('/pruebas-ds/public/register');
        }

        $nombre = $this->request->get('nombre');
        $apellidos = $this->request->get('apellidos');
        $email = $this->request->get('correo');
        $password = $this->request->get('contra');

        // Validación básica
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            return $this->redirect('/pruebas-ds/public/register');
        }

        try {
            // Verificar si el email ya existe
            if ($this->userRepository->emailExists($email)) {
                $_SESSION['error'] = 'El correo electrónico ya está registrado.';
                return $this->redirect('/pruebas-ds/public/register');
            }
            
            // Crear nuevo usuario
            $user = new User(null, $nombre, $apellidos, $email, $password);
            
            // Guardar usuario
            if ($this->userRepository->save($user)) {
                // Obtener el usuario guardado para obtener su ID
                $savedUser = $this->userRepository->findByEmail($email);
                if (!$savedUser) {
                    throw new \Exception("Error: No se pudo obtener la información del usuario después de guardarlo.");
                }

                // Autenticar al usuario
                $this->authenticator->authenticate($email, $password);
    
                // Guardar datos en la sesión
                $_SESSION['correo'] = $email;
                $_SESSION['user_id'] = $savedUser->getId();
    
                // Log para depuración
                error_log('Usuario registrado y autenticado - ID: ' . $savedUser->getId() . ', Correo: ' . $email);
    
                return $this->redirect('/pruebas-ds/public/dashboard');
            }
            else {
                $_SESSION['error'] = 'Error al crear el usuario. Por favor, inténtalo de nuevo.';
                return $this->redirect('/pruebas-ds/public/register');
            }
        } catch (\Exception $e) {
            // Log del error
            error_log('Error de registro: ' . $e->getMessage());
            $_SESSION['error'] = 'Error en el sistema. Por favor, inténtalo más tarde.';
            return $this->redirect('/pruebas-ds/public/register');
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
        session_unset();
        session_destroy();
        
        // Redirigir a la página de inicio
        return $this->redirect('/pruebas-ds/public');
    }
}