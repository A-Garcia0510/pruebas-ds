<?php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>
<header>
    <nav class="navbar">
        <div class="logo">
            <h1>Ethos<span>Coffee</span></h1>
        </div>
        <ul class="main-menu">
            <li><a href="<?= AssetHelper::url() ?>">Inicio</a></li>
            <li><a href="<?= AssetHelper::url('productos') ?>">Productos</a></li>
            <li><a href="<?= AssetHelper::url('servicios') ?>">Servicios</a></li>
            <li><a href="<?= AssetHelper::url('ayuda') ?>">Ayuda</a></li>
        </ul>
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= AssetHelper::url('dashboard') ?>" class="icon">👤</a>
                <a href="<?= AssetHelper::url('logout') ?>" class="icon">🚪</a>
            <?php else: ?>
                <a href="<?= AssetHelper::url('login') ?>" class="icon">👤</a>
            <?php endif; ?>
            <a href="<?= AssetHelper::url('carrito') ?>" class="icon">🛒</a>
        </div>
    </nav>
</header>