<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/Database.php';
require_once '../classes/Repositories/UserRepository.php';
require_once '../classes/Validators/UserValidator.php';
require_once '../classes/SessionManagers/PHPSessionManager.php';
require_once '../classes/Services/UserService.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario y sanitizar
    $userData = [
        'nombre' => trim($_POST['nombre']),
        'apellidos' => trim($_POST['apellidos']),
        'correo' => trim($_POST['correo']),
        'contraseña' => $_POST['contra']
    ];
    
    $db = Database::getInstance();
    $userRepository = new UserRepository($db);
    $userValidator = new UserValidator();
    $sessionManager = new PHPSessionManager();
    
    $userService = new UserService($userRepository, $userValidator, $sessionManager);
    
    $resultado = $userService->register($userData);
    
    if ($resultado['success']) {
        echo "<script>
                alert('{$resultado['message']}');
                window.location.href='../login.html';
             </script>";
        exit();
    } else {
        echo "<script>
                alert('{$resultado['message']}');
                window.history.back();
             </script>";
    }
}
?>