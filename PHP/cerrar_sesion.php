<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Repositories/UserRepository.php';
require_once '../classes/Validators/UserValidator.php';
require_once '../classes/SessionManagers/PHPSessionManager.php';
require_once '../classes/Services/UserService.php';

$db = Database::getInstance();
$userRepository = new UserRepository($db);
$userValidator = new UserValidator();
$sessionManager = new PHPSessionManager();

$userService = new UserService($userRepository, $userValidator, $sessionManager);
$userService->logout();

// Redirigir a la página de inicio de sesión
echo "<script>
          alert('Sesion Cerrada.');
          window.location.href='../login.html';
      </script>";
exit();
?>