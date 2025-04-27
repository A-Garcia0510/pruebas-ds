<?php
// src/MVC/Controllers/Auth/RegisterController.php
namespace App\MVC\Controllers\Auth;

use App\Auth\AuthFactory;
use App\Auth\Models\User;
use App\Core\Database\DatabaseConfiguration;
use App\Core\Database\MySQLDatabase;
use App\MVC\Controllers\BaseController;

class RegisterController extends BaseController
{
    private $userRepository;
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
            
            // Crear el repositorio de usuarios
            $this->userRepository = AuthFactory::createUserRepository($database);
            
            // Crear el autenticador
            $this->authenticator = AuthFactory::createAuthenticator($database);
        } catch (\Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function showRegisterForm(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->authenticator->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('Auth/register');
    }
    
    /**
     * Procesa el formulario de registro
     */
    public function register(): void
    {
        // Si no es una petición POST, redirigir al formulario
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }
        
        // Obtener datos del formulario
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $email = $_POST['correo'] ?? '';
        $password = $_POST['contra'] ?? '';
        $terms = isset($_POST['terms']);
        
        // Validaciones básicas
        $errors = [];
        
        if (empty($nombre)) {
            $errors[] = 'El nombre es obligatorio';
        }
        
        if (empty($apellidos)) {
            $errors[] = 'Los apellidos son obligatorios';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico es inválido';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!$terms) {
            $errors[] = 'Debes aceptar los términos y condiciones';
        }
        
        // Si hay errores, volver al formulario
        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_data'] = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'correo' => $email
            ];
            $this->redirect('/register');
            return;
        }
        
        // Verificar si el email ya existe
        if ($this->userRepository->emailExists($email)) {
            $_SESSION['register_errors'] = ['El correo electrónico ya está registrado'];
            $_SESSION['register_data'] = [
                'nombre' => $nombre,
                'apellidos' => $apellidos
            ];
            $this->redirect('/register');
            return;
        }
        
        // Crear y guardar el usuario
        $user = new User(null, $nombre, $apellidos, $email, $password); // En un sistema real, deberías hashear la contraseña
        
        if ($this->userRepository->save($user)) {
            // Iniciar sesión automáticamente
            if ($this->authenticator->authenticate($email, $password)) {
                $this->redirect('/dashboard');
            } else {
                $_SESSION['success_message'] = 'Registro exitoso. Por favor, inicia sesión.';
                $this->redirect('/login');
            }
        } else {
            $_SESSION['register_errors'] = ['Error al registrar el usuario. Inténtalo de nuevo.'];
            $this->redirect('/register');
        }
    }
}