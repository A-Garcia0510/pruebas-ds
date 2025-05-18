<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Café-VT' ?></title>
    <!-- CSS Base -->
    <link rel="stylesheet" href="<?= \App\Helpers\SimpleAssetManager::css('main') ?>">
    <!-- CSS Específicos -->
    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $stylesheet): ?>
            <link rel="stylesheet" href="<?= \App\Helpers\SimpleAssetManager::css($stylesheet) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="<?= \App\Helpers\SimpleAssetManager::url() ?>">Café-VT</a>
            </div>
            <ul class="nav-links">
                <li><a href="<?= \App\Helpers\SimpleAssetManager::url() ?>">Inicio</a></li>
                <li><a href="<?= \App\Helpers\SimpleAssetManager::url('productos') ?>">Productos</a></li>
                <li><a href="<?= \App\Helpers\SimpleAssetManager::url('servicios') ?>">Servicios</a></li>
                <li><a href="<?= \App\Helpers\SimpleAssetManager::url('ayuda') ?>">Ayuda</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('dashboard') ?>">Mi Cuenta</a></li>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('logout') ?>">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('login') ?>">Iniciar Sesión</a></li>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('registro') ?>">Registrarse</a></li>
                <?php endif; ?>
                <li><a href="<?= \App\Helpers\SimpleAssetManager::url('carrito') ?>">Carrito</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Café-VT</h3>
                <p>Tu tienda en línea para productos de café de alta calidad</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces rápidos</h3>
                <ul>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url() ?>">Inicio</a></li>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('productos') ?>">Productos</a></li>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('servicios') ?>">Servicios</a></li>
                    <li><a href="<?= \App\Helpers\SimpleAssetManager::url('ayuda') ?>">Ayuda</a></li>
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

    <!-- JavaScript -->
    <?php if (isset($js) && is_array($js)): ?>
        <?php foreach ($js as $script): ?>
            <script src="<?= \App\Helpers\SimpleAssetManager::js($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>