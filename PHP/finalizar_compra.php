<?php
session_start();
require_once '../classes/Purchase.php';

if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no logueado.']);
    exit();
}

$correoUsuario = $_SESSION['correo'];

// Crear instancia de la clase Purchase
$purchaseObj = new Purchase();

// Crear la compra
$result = $purchaseObj->createPurchase($correoUsuario);

echo json_encode($result);
?>