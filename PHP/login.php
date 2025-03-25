<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/Database.php';
require_once '../classes/Repositories/UserRepository.php';
require_once '../classes/Validators/UserValidator.php';
require_once '../classes/SessionManagers/PHPSessionManager.php';
require_once '../classes/Services/UserService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['correo'];
    $password = $_POST['contra'];
    
    $db = Database::getInstance();
    $userRepository = new UserRepository($db);
    $userValidator = new UserValidator();
    $sessionManager = new PHPSessionManager();
    
    $userService = new UserService($userRepository, $userValidator, $sessionManager);
    
    $loginSuccess = $userService->login($email, $password);
    
    if ($loginSuccess) {
        header("Location:../PHP/visual_datos.php");
        exit();
    } else {
        echo "<script>
                alert('Datos incorrectos. Por favor, inténtalo de nuevo.'); 
                window.location.href='../login.html';
                </script>";
    }
}
?>