<?php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>
<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3>Café-VT</h3>
            <p>Tu tienda en línea para productos de café de alta calidad</p>
        </div>
        <div class="footer-section">
            <h3>Enlaces rápidos</h3>
            <ul>
                <li><a href="<?= AssetHelper::url() ?>">Inicio</a></li>
                <li><a href="<?= AssetHelper::url('productos') ?>">Productos</a></li>
                <li><a href="<?= AssetHelper::url('servicios') ?>">Servicios</a></li>
                <li><a href="<?= AssetHelper::url('ayuda') ?>">Ayuda</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contacto</h3>
            <p>Email: info@cafe-vt.com</p>
            <p>Teléfono: (123) 456-7890</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Café-VT - Todos los derechos reservados</p>
    </div>
</footer>