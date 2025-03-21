<?php
session_start();
include '../conexion/conexion.inc';

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];
$sqlUsuario = "SELECT usuario_ID FROM Usuario WHERE correo = '$correoUsuario'";
$resultUsuario = $conn->query($sqlUsuario);

if ($resultUsuario->num_rows > 0) {
    $usuarioID = $resultUsuario->fetch_assoc()['usuario_ID'];

    // Verificamos si el carrito está vacío antes de proceder con la compra
    $sqlVerificarCarrito = "SELECT COUNT(*) AS total FROM Carro WHERE usuario_ID = $usuarioID";
    $resultVerificarCarrito = $conn->query($sqlVerificarCarrito);
    $row = $resultVerificarCarrito->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit();
    }

    // Crear la compra
    $sqlCrearCompra = "INSERT INTO Compra (usuario_ID, fecha_compra, total) 
                       SELECT $usuarioID, NOW(), SUM(p.precio * c.cantidad) 
                       FROM Carro c 
                       JOIN Producto p ON c.producto_ID = p.producto_ID 
                       WHERE c.usuario_ID = $usuarioID";

    if ($conn->query($sqlCrearCompra)) {
        $compraID = $conn->insert_id;

        // Crear los detalles de la compra
        $sqlDetalles = "INSERT INTO Detalle_Compra (compra_ID, producto_ID, cantidad, precio_unitario) 
                        SELECT $compraID, c.producto_ID, c.cantidad, p.precio 
                        FROM Carro c 
                        JOIN Producto p ON c.producto_ID = p.producto_ID 
                        WHERE c.usuario_ID = $usuarioID";

        $conn->query($sqlDetalles);

        // Actualizar el stock de los productos
        $sqlActualizarStock = "UPDATE Producto p
                               JOIN Carro c ON p.producto_ID = c.producto_ID
                               SET p.cantidad = p.cantidad - c.cantidad
                               WHERE c.usuario_ID = $usuarioID";
        $conn->query($sqlActualizarStock);

        // Limpiar el carrito
        $sqlLimpiarCarro = "DELETE FROM Carro WHERE usuario_ID = $usuarioID";
        $conn->query($sqlLimpiarCarro);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar la compra.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
}
?>
