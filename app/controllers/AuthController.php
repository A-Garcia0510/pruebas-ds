<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Auth\Models\User;
use App\Auth\AuthFactory;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión
     */
    public function showLoginForm()
    {
        // Si ya está autenticado, redirigir a la página principal
        if ($this->isAuthenticated()) {
            $this->redirect('/');
        }
        
        $this->render('auth/login.php');
    }
    
    /**
     * Procesa el inicio de sesión
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['correo'] ?? '';
            $password = $_POST['contra'] ?? '';
            
            try {
                if ($this->authenticator->authenticate($email, $password)) {
                    $this->redirect('/perfil');
                } else {
                    // Almacenar mensaje de error y redirigir al formulario
                    $_SESSION['error'] = 'Datos incorrectos. Por favor, inténtalo de nuevo.';
                    $this->redirect('/login');
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error en el sistema: ' . $e->getMessage();
                $this->redirect('/login');
            }
        } else {
            // Si no es POST, redirigir al formulario
            $this->redirect('/login');
        }
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function showRegisterForm()
    {
        // Si ya está autenticado, redirigir a la página principal
        if ($this->isAuthenticated()) {
            $this->redirect('/');
        }
        
        $this->render('auth/register.php');
    }
    
    /**
     * Procesa el registro de un nuevo usuario
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener datos del formulario
            $nombre = $_POST['nombre'] ?? '';
            $apellidos = $_POST['apellidos'] ?? '';
            $email = $_POST['correo'] ?? '';
            $password = $_POST['contra'] ?? '';
            
            try {
                // Crear el repositorio de usuarios
                $userRepository = AuthFactory::createUserRepository($this->database);
                
                // Validar que todos los campos están completos
                if (empty($nombre) || empty($apellidos) || empty($email) || empty($password)) {
                    $_SESSION['error'] = 'Todos los campos son obligatorios.';
                    $this->redirect('/registro');
                    return;
                }
                
                // Verificar si el email ya existe
                if ($userRepository->emailExists($email)) {
                    $_SESSION['error'] = 'El correo electrónico ya está registrado.';
                    $this->redirect('/registro');
                    return;
                }
                
                // Crear nuevo usuario
                $user = new User(null, $nombre, $apellidos, $email, $password);
                
                // Guardar usuario
                if ($userRepository->save($user)) {
                    // Autenticar al usuario
                    $this->authenticator->authenticate($email, $password);
                    $this->redirect('/perfil');
                } else {
                    $_SESSION['error'] = 'Error al crear el usuario. Por favor, inténtalo de nuevo.';
                    $this->redirect('/registro');
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error en el sistema: ' . $e->getMessage();
                $this->redirect('/registro');
            }
        } else {
            // Si no es POST, redirigir al formulario
            $this->redirect('/registro');
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        try {
            // Cerrar sesión
            $this->authenticator->logout();
            
            // Redirigir al login
            $this->redirect('/login');
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al cerrar sesión: ' . $e->getMessage();
            $this->redirect('/');
        }
    }
}