<?php
session_start();
require_once '../classes/Cart.php';

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];

// Crear instancia de la clase Cart
$cartObj = new Cart();

// Obtener los productos del carrito
$carrito = $cartObj->getCartItems($correoUsuario);

if (!empty($carrito)) {
    echo json_encode(['success' => true, 'carrito' => $carrito]);
} else {
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
}
?>