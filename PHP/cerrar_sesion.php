<?php
session_start();
require_once '../classes/User.php';

$user = new User();
$user->logout();

// Redirigir a la página de inicio de sesión
echo "<script>
          alert('Sesion Cerrada.');
          window.location.href='../login.html';
      </script>";
exit();
?>