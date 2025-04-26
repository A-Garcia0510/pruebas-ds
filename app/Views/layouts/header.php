<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Aroma</title>
    <link rel="stylesheet" href="/css/styles.css">
    <?php
    // Carga dinámica de CSS específicos según la página
    $currentPage = $_SERVER['REQUEST_URI'];
    if (strpos($currentPage, 'login') !== false) {
        echo '<link rel="stylesheet" href="/css/login.css">';
    } elseif (strpos($currentPage, 'registro') !== false) {
        echo '<link rel="stylesheet" href="/css/registro.css">';
    } elseif ($currentPage === '/' || $currentPage === '/index.php') {
        echo '<link rel="stylesheet" href="/css/index.css">';
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Café<span>Aroma</span></h1>
            </div>
            <ul class="main-menu">
                <li><a href="/">Inicio</a></li>
                <li><a href="/productos">Menú</a></li>
                <li><a href="/servicios">Sobre Nosotros</a></li>
            </ul>
            <div class="user-actions">
                <div class="icon">👤</div>
                <div class="icon">❤️</div>
                <div class="icon">🛒</div>
                <?php if (isset($_SESSION['correo'])): ?>
                    <a href="/logout" class="btn-logout">Cerrar sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>