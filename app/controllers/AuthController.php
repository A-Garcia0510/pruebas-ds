<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Auth\AuthFactory;
use App\Auth\Models\User;

/**
 * Controlador para gestionar la autenticación de usuarios
 */
class AuthController extends BaseController
{
    private $authenticator;
    private $userRepository;
    
    /**
     * Constructor del controlador de autenticación
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
     * Muestra el formulario de inicio de sesión
     */
    public function showLoginForm()
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authenticator->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        return $this->render('auth/login', [
            'title' => 'Iniciar Sesión',
            'css' => ['login']
        ]);
    }
    
    /**
     * Procesa el inicio de sesión
     */
    public function login()
    {
        // Si la solicitud no es POST, redirigir al formulario
        if (!$this->request->isPost()) {
            $this->redirect('/login');
            return;
        }
        
        // Obtener datos del formulario
        $correo = $this->request->get('correo');
        $password = $this->request->get('contra');
        
        // Intentar autenticar al usuario
        if ($this->authenticator->authenticate($correo, $password)) {
            $this->redirect('/dashboard');
        } else {
            // Si la autenticación falla, volver al formulario con mensaje de error
            return $this->render('auth/login', [
                'title' => 'Iniciar Sesión',
                'css' => ['login'],
                'error' => 'Correo electrónico o contraseña incorrectos'
            ]);
        }
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function showRegisterForm()
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authenticator->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        return $this->render('auth/register', [
            'title' => 'Registro',
            'css' => ['registro']
        ]);
    }
    
    /**
     * Procesa el registro de un nuevo usuario
     */
    public function register()
    {
        // Si la solicitud no es POST, redirigir al formulario
        if (!$this->request->isPost()) {
            $this->redirect('/registro');
            return;
        }
        
        // Obtener datos del formulario
        $nombre = $this->request->get('nombre');
        $apellidos = $this->request->get('apellidos');
        $correo = $this->request->get('correo');
        $password = $this->request->get('contra');
        
        // Verificar si ya existe un usuario con ese correo
        if ($this->userRepository->emailExists($correo)) {
            return $this->render('auth/register', [
                'title' => 'Registro',
                'css' => ['registro'],
                'error' => 'El correo electrónico ya está registrado',
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'correo' => $correo
            ]);
        }
        
        // Crear y guardar el nuevo usuario
        $user = new User(null, $nombre, $apellidos, $correo, $password);
        
        if ($this->userRepository->save($user)) {
            // Autenticar al usuario después del registro
            $this->authenticator->authenticate($correo, $password);
            $this->redirect('/dashboard');
        } else {
            return $this->render('auth/register', [
                'title' => 'Registro',
                'css' => ['registro'],
                'error' => 'Error al registrar el usuario',
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'correo' => $correo
            ]);
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        $this->authenticator->logout();
        $this->redirect('/');
    }
}