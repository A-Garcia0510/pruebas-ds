<?php
session_start();
require_once '../classes/Cart.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['correo'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $productoID = $data['producto_ID'];
    $cantidad = $data['cantidad'];
    $correoUsuario = $_SESSION['correo'];
    
    // Crear instancia de la clase Cart
    $cartObj = new Cart();
    
    // Agregar producto al carrito
    $result = $cartObj->addToCart($correoUsuario, $productoID, $cantidad);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>