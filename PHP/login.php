<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'autoload.php';

use Entities\Email;
use Services\AuthenticationService;
use Repositories\DatabaseUserRepository;
use Services\UserValidationService;
use Exceptions\InvalidCredentialsException;
use Exceptions\ValidationException;
use Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize input
        $email = trim($_POST['correo']);
        $password = $_POST['contra'];
        
        // Initialize dependencies
        $database = Database::getInstance();
        $userRepository = new DatabaseUserRepository($database);
        $userValidation = new UserValidationService();
        $authService = new AuthenticationService($userRepository, $userValidation);
        
        // Create email value object
        $emailObj = new Email($email);
        
        // Attempt login
        $user = $authService->login($emailObj, $password);
        
        // Redirect to user dashboard
        $_SESSION['correo'] = $user->getEmail()->getValue();
        header("Location: visual_datos.php");
        exit();
        
    } catch (InvalidCredentialsException $e) {
        echo "<script>
                alert('Credenciales inválidas. Por favor, inténtalo de nuevo.');
                window.location.href='../login.html';
             </script>";
    } catch (ValidationException $e) {
        echo "<script>
                alert('" . $e->getMessage() . "');
                window.location.href='../login.html';
             </script>";
    } catch (Exception $e) {
        echo "<script>
                alert('Error inesperado: " . $e->getMessage() . "');
                window.location.href='../login.html';
             </script>";
    }
}
?>