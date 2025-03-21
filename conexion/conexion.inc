<?php
   $servidor = "mysql.inf.uct.cl";
   $user     = "agarcia";
   $password = "chuMKZ3EdhJvje706";
   $basedato = "A2024_agarcia";
   
   
   
// Crear conexión
$conn= new mysqli($servidor, $user, $password, $basedato);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>