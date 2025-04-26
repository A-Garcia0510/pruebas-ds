<?php
// src/MVC/Controllers/Auth/LoginController.php
namespace App\MVC\Controllers\Auth;

use App\MVC\Controllers\BaseController;
use App\Auth\Services\Authenticator;
use App\Core\Database\DatabaseInterface;

class LoginController extends BaseController
{
    /**
     * Constructor
     * 
     * @param DatabaseInterface $db
     * @param Authenticator $auth
     */
    public function __construct(DatabaseInterface $db, Authenticator $auth)
    {
        parent::__construct($db, $auth);
    }
    
    /**
     * Muestra el formulario de login
     */
    public function showLoginForm(): void
    {
        // Si ya está autenticado, redirigir al inicio
        if ($this->auth->isAuthenticated()) {
            $this->redirect('/');
            return;
        }
        
        // Pasar el mensaje de error si existe
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        
        $this->render('Auth/login', [
            'error' => $error,
            'layout' => 'main'
        ]);
    }
    
    /**
     * Procesa el formulario de login
     */
    public function login(): void
    {
        $email = $this->post('correo', '');
        $password = $this->post('contra', '');
        
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Por favor, complete todos los campos';
            $this->redirect('/login');
            return;
        }
        
        // Intentar autenticar al usuario
        if ($this->auth->authenticate($email, $password)) {
            $this->redirect('/'); // Redirigir al inicio si es exitoso
        } else {
            $_SESSION['login_error'] = 'Credenciales incorrectas';
            $this->redirect('/login');
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/');
    }
}