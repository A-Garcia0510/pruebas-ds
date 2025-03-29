<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['correo'];
    $password = $_POST['contra'];
    
    $user = new User();
    $loginSuccess = $user->login($email, $password);
    
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