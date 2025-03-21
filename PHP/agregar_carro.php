<?php
session_start();
include '../conexion/conexion.inc';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productoID = $data['producto_ID'];
    $cantidad = $data['cantidad'];
    $correoUsuario = $_SESSION['correo'];

    $usuarioSql = "SELECT usuario_ID FROM Usuario WHERE correo = '$correoUsuario'";
    $usuarioResult = $conn->query($usuarioSql);
    if ($usuarioResult->num_rows > 0) {
        $usuarioID = $usuarioResult->fetch_assoc()['usuario_ID'];

        $carroSql = "SELECT cantidad FROM Carro WHERE usuario_ID = $usuarioID AND producto_ID = $productoID";
        $carroResult = $conn->query($carroSql);

        if ($carroResult->num_rows > 0) {
            // Actualizar cantidad
            $carroRow = $carroResult->fetch_assoc();
            $nuevaCantidad = $carroRow['cantidad'] + $cantidad;
            $updateSql = "UPDATE Carro SET cantidad = $nuevaCantidad WHERE usuario_ID = $usuarioID AND producto_ID = $productoID";
            $conn->query($updateSql);
        } else {
            // Insertar nuevo producto
            $insertSql = "INSERT INTO Carro (usuario_ID, producto_ID, cantidad) VALUES ($usuarioID, $productoID, $cantidad)";
            $conn->query($insertSql);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
