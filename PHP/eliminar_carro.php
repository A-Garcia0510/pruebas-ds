<?php
session_start();
require_once '../classes/Cart.php';

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];
$data = json_decode(file_get_contents('php://input'));
$productoID = $data->producto_ID;

if (isset($productoID)) {
    // Crear instancia de la clase Cart
    $cartObj = new Cart();
    
    // Eliminar producto del carrito
    $result = $cartObj->removeFromCart($correoUsuario, $productoID);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
}
?>