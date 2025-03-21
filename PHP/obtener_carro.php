<?php
session_start();
include '../conexion/conexion.inc';

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];

$sql = "SELECT u.usuario_ID, p.producto_ID, p.nombre_producto, p.precio, c.cantidad
        FROM Carro c
        JOIN Usuario u ON c.usuario_ID = u.usuario_ID
        JOIN Producto p ON c.producto_ID = p.producto_ID
        WHERE u.correo = '$correoUsuario'";

$result = $conn->query($sql);
$carrito = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $carrito[] = $row;
    }
    echo json_encode(['success' => true, 'carrito' => $carrito]);
} else {
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
}
?>
