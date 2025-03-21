<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location:../login.html");
echo "<script>
            alert('Sesion Cerrada.');
            window.location.href='../login.html';
        </script>";
exit();
?>
