<?php
session_start();
include '../conexion/conexion.inc';

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];
$productoID = json_decode(file_get_contents('php://input'))->producto_ID;

if (isset($productoID)) {
    // Obtener el usuario_ID del correo
    $sqlUsuario = "SELECT usuario_ID FROM Usuario WHERE correo = '$correoUsuario'";
    $resultUsuario = $conn->query($sqlUsuario);

    if ($resultUsuario->num_rows > 0) {
        $usuarioID = $resultUsuario->fetch_assoc()['usuario_ID'];

        // Eliminar el producto del carrito
        $sqlEliminar = "DELETE FROM Carro WHERE usuario_ID = $usuarioID AND producto_ID = $productoID";
        if ($conn->query($sqlEliminar)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto del carrito.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
}
?>
