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
            <li><a href="<?= AssetHelper::url('custom-coffee') ?>">Café Personalizado</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" onclick="return false;">
                    Fidelización <span class="dropdown-arrow">▼</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?= AssetHelper::url('loyalty') ?>">🏠 Dashboard</a></li>
                    <li><a href="<?= AssetHelper::url('loyalty/profile') ?>">👤 Mi Perfil</a></li>
                    <li><a href="<?= AssetHelper::url('loyalty/rewards') ?>">🎁 Recompensas</a></li>
                    <li><a href="<?= AssetHelper::url('loyalty/transactions') ?>">📊 Historial</a></li>
                </ul>
            </li>
            <li><a href="<?= AssetHelper::url('servicios') ?>">Servicios</a></li>
            <li><a href="<?= AssetHelper::url('ayuda') ?>">Ayuda</a></li>
        </ul>
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'Empleado' || $_SESSION['rol'] === 'Administrador')): ?>
                    <a href="<?= AssetHelper::url('dashboard/moderation') ?>" class="icon" title="Moderación">⚖️</a>
                <?php endif; ?>
                <a href="<?= AssetHelper::url('dashboard') ?>" class="icon" title="Mi Cuenta">👤</a>
                <a href="<?= AssetHelper::url('custom-coffee/recipes') ?>" class="icon" title="Mis Recetas">📋</a>
                <a href="<?= AssetHelper::url('custom-coffee/orders') ?>" class="icon" title="Mis Pedidos">📦</a>
                <a href="<?= AssetHelper::url('carrito') ?>" class="icon" title="Carrito">🛒</a>
                <a href="<?= AssetHelper::url('logout') ?>" class="icon" title="Cerrar Sesión">🚪</a>
            <?php else: ?>
                <a href="<?= AssetHelper::url('login') ?>" class="icon" title="Iniciar Sesión">👤</a>
                <a href="<?= AssetHelper::url('carrito') ?>" class="icon" title="Carrito">🛒</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- Scripts de fidelización -->
<?php if (isset($_SESSION['user_id'])): ?>
    <script src="<?= AssetHelper::js('loyalty-api') ?>"></script>
<?php endif; ?>