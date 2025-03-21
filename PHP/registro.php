<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../classes/User.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario y sanitizar
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $contra = $_POST['contra'];
    
    $user = new User();
    $resultado = $user->register($nombre, $apellidos, $correo, $contra);
    
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