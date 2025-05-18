<?php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>
<header>
    <nav>
        <div class="logo">
            <a href="<?= AssetHelper::url() ?>">Café-VT</a>
        </div>
        <ul class="nav-links">
            <li><a href="<?= AssetHelper::url() ?>">Inicio</a></li>
            <li><a href="<?= AssetHelper::url('productos') ?>">Productos</a></li>
            <li><a href="<?= AssetHelper::url('servicios') ?>">Servicios</a></li>
            <li><a href="<?= AssetHelper::url('ayuda') ?>">Ayuda</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?= AssetHelper::url('dashboard') ?>">Mi Cuenta</a></li>
                <li><a href="<?= AssetHelper::url('logout') ?>">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="<?= AssetHelper::url('login') ?>">Iniciar Sesión</a></li>
                <li><a href="<?= AssetHelper::url('registro') ?>">Registrarse</a></li>
            <?php endif; ?>
            <li><a href="<?= AssetHelper::url('carrito') ?>">Carrito</a></li>
        </ul>
    </nav>
</header>