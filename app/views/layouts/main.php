<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Café-VT' ?></title>
    <!-- Incluir CSS principales con ruta absoluta usando URL base -->
    <link rel="stylesheet" href="<?= $config['app']['url'] ?>/css/main.css">
    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $stylesheet): ?>
            <link rel="stylesheet" href="<?= $config['app']['url'] ?>/css/<?= $stylesheet ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="<?= $config['app']['url'] ?>/">Café-VT</a>
            </div>
            <ul class="nav-links">
                <li><a href="<?= $config['app']['url'] ?>/">Inicio</a></li>
                <li><a href="<?= $config['app']['url'] ?>/productos">Productos</a></li>
                <li><a href="<?= $config['app']['url'] ?>/servicios">Servicios</a></li>
                <li><a href="<?= $config['app']['url'] ?>/ayuda">Ayuda</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= $config['app']['url'] ?>/dashboard">Mi Cuenta</a></li>
                    <li><a href="<?= $config['app']['url'] ?>/logout">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="<?= $config['app']['url'] ?>/login">Iniciar Sesión</a></li>
                    <li><a href="<?= $config['app']['url'] ?>/registro">Registrarse</a></li>
                <?php endif; ?>
                <li><a href="<?= $config['app']['url'] ?>/carrito">Carrito</a></li>
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
                    <li><a href="<?= $config['app']['url'] ?>/">Inicio</a></li>
                    <li><a href="<?= $config['app']['url'] ?>/productos">Productos</a></li>
                    <li><a href="<?= $config['app']['url'] ?>/servicios">Servicios</a></li>
                    <li><a href="<?= $config['app']['url'] ?>/ayuda">Ayuda</a></li>
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

    <!-- Incluir JavaScript con ruta absoluta -->
    <?php if (isset($js) && is_array($js)): ?>
        <?php foreach ($js as $script): ?>
            <script src="<?= $config['app']['url'] ?>/js/<?= $script ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>