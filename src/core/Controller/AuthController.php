<?php
// src/core/Controller/AuthController.php
namespace App\Core\Controller;

use App\Auth\AuthFactory;
use App\Auth\Models\User;
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseConfiguration;

class AuthController extends BaseController {
    protected $authenticator;
    protected $userRepository;
    protected $database;
    
    public function __construct() {
        parent::__construct();
        
        // Cargar configuración
        $config = require_once __DIR__ . '/../../Config/Config.php';
        $dbConfig = new DatabaseConfiguration(
            $config['database']['host'],
            $config['database']['username'],
            $config['database']['password'],
            $config['database']['database']
        );
        
        // Crear conexión a la base de datos
        try {
            $this->database = new MySQLDatabase($dbConfig);
            $this->authenticator = AuthFactory::createAuthenticator($this->database);
            $this->userRepository = AuthFactory::createUserRepository($this->database);
        } catch (\Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Muestra el formulario de login
     */
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authenticator->isAuthenticated()) {
            header("Location: " . $this->router->getBaseUrl() . "/auth/dashboard");
            exit();
        }
        
        $this->render('auth/login');
    }
    
    /**
     * Procesa el formulario de login
     */
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . $this->router->getBaseUrl() . "/auth/login");
            exit();
        }
        
        $email = $_POST['correo'] ?? '';
        $password = $_POST['contra'] ?? '';
        
        if ($this->authenticator->authenticate($email, $password)) {
            header("Location: " . $this->router->getBaseUrl() . "/auth/dashboard");
            exit();
        } else {
            // Mensaje de error
            $data = [
                'error' => 'Datos incorrectos. Por favor, inténtalo de nuevo.'
            ];
            $this->render('auth/login', $data);
        }
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function register() {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authenticator->isAuthenticated()) {
            header("Location: " . $this->router->getBaseUrl() . "/auth/dashboard");
            exit();
        }
        
        $this->render('auth/register');
    }
    
    /**
     * Procesa el formulario de registro
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . $this->router->getBaseUrl() . "/auth/register");
            exit();
        }
        
        // Obtener datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $email = $_POST['correo'] ?? '';
        $password = $_POST['contra'] ?? '';
        
        // Validar que todos los campos están completos
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            $data = [
                'error' => 'Todos los campos son obligatorios.',
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'correo' => $email
            ];
            $this->render('auth/register', $data);
            return;
        }
        
        // Verificar si el email ya existe
        if ($this->userRepository->emailExists($email)) {
            $data = [
                'error' => 'El correo electrónico ya está registrado.',
                'nombre' => $nombre,
                'apellidos' => $apellidos
            ];
            $this->render('auth/register', $data);
            return;
        }
        
        // Crear nuevo usuario
        $user = new User(null, $nombre, $apellidos, $email, $password);
        
        // Guardar usuario
        if ($this->userRepository->save($user)) {
            // Autenticar al usuario
            $this->authenticator->authenticate($email, $password);
            
            header("Location: " . $this->router->getBaseUrl() . "/auth/dashboard");
            exit();
        } else {
            $data = [
                'error' => 'Error al crear el usuario. Por favor, inténtalo de nuevo.',
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'correo' => $email
            ];
            $this->render('auth/register', $data);
        }
    }
    
    /**
     * Muestra la página del dashboard del usuario
     */
    public function dashboard() {
        // Verificar si el usuario está autenticado
        if (!$this->authenticator->isAuthenticated()) {
            header("Location: " . $this->router->getBaseUrl() . "/auth/login");
            exit();
        }
        
        // Obtener email del usuario actual
        $correo = $this->authenticator->getCurrentUserEmail();
        
        // Obtener los datos del usuario
        $user = $this->userRepository->findByEmail($correo);
        
        if (!$user) {
            header("Location: " . $this->router->getBaseUrl() . "/auth/logout");
            exit();
        }
        
        $data = [
            'user' => $user
        ];
        
        $this->render('auth/dashboard', $data);
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        $this->authenticator->logout();
        header("Location: " . $this->router->getBaseUrl() . "/auth/login");
        exit();
    }
}