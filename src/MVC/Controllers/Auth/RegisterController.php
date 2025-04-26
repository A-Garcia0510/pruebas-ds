<?php
// src/MVC/Controllers/Auth/RegisterController.php
namespace App\MVC\Controllers\Auth;

use App\MVC\Controllers\BaseController;
use App\Auth\Models\User;
use App\Auth\Interfaces\UserRepositoryInterface;
use App\Core\Database\DatabaseInterface;
use App\Auth\Services\Authenticator;

class RegisterController extends BaseController
{
    private $userRepository;
    
    /**
     * Constructor
     * 
     * @param DatabaseInterface $db
     * @param Authenticator $auth
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        DatabaseInterface $db, 
        Authenticator $auth,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($db, $auth);
        $this->userRepository = $userRepository;
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function showRegistrationForm(): void
    {
        // Si ya está autenticado, redirigir al inicio
        if ($this->auth->isAuthenticated()) {
            $this->redirect('/');
            return;
        }
        
        // Pasar el mensaje de error si existe
        $error = $_SESSION['register_error'] ?? null;
        unset($_SESSION['register_error']);
        
        $this->render('Auth/register', [
            'error' => $error,
            'layout' => 'main'
        ]);
    }
    
    /**
     * Procesa el formulario de registro
     */
    public function register(): void
    {
        $nombre = $this->post('nombre', '');
        $apellidos = $this->post('apellidos', '');
        $email = $this->post('correo', '');
        $password = $this->post('contra', '');
        
        // Validación básica
        if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
            $_SESSION['register_error'] = 'Por favor, complete todos los campos';
            $this->redirect('/register');
            return;
        }
        
        // Verificar si el email ya existe
        if ($this->userRepository->emailExists($email)) {
            $_SESSION['register_error'] = 'El correo electrónico ya está registrado';
            $this->redirect('/register');
            return;
        }
        
        // En un sistema real, se debería hashear la contraseña antes de guardarla
        $user = new User(null, $nombre, $apellidos, $email, $password);
        
        if ($this->userRepository->save($user)) {
            // Auto-login después del registro
            $this->auth->authenticate($email, $password);
            $this->redirect('/');
        } else {
            $_SESSION['register_error'] = 'Error al registrar el usuario';
            $this->redirect('/register');
        }
    }
}