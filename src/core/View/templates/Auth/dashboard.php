<?php
// src/core/View/auth/dashboard.php
$baseUrl = $this->router->getBaseUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Aroma - Mi Cuenta</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/CSS/dashboard.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Ethos<span>Coffe</span></h1>
            </div>
            <ul class="main-menu">
                <li><a href="<?php echo $baseUrl; ?>/">Inicio</a></li>
                <li><a href="<?php echo $baseUrl; ?>/PHP/productos.php">Menú</a></li>
                <li><a href="<?php echo $baseUrl; ?>/servicios">Sobre Nosotros</a></li>
            </ul>
            <div class="user-actions">
                <div class="icon">👤</div>
                <div class="icon">❤️</div>
                <div class="icon">🛒</div>
            </div>
        </nav>
    </header>
    
    <section class="user-data-section">
        <div class="container">
            <div class="section-title">
                <h2>Mi Cuenta</h2>
            </div>
            <p class="welcome-message">¡Bienvenido, <?php echo htmlspecialchars($user->getNombre()); ?>!</p>
            
            <table class="user-data-table">
                <thead>
                    <tr>
                        <th>Datos</th>
                        <th>Información</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nombre:</td>
                        <td><?php echo htmlspecialchars($user->getNombre()); ?></td>
                    </tr>
                    <tr>
                        <td>Apellidos:</td>
                        <td><?php echo htmlspecialchars($user->getApellidos()); ?></td>
                    </tr>
                    <tr>
                        <td>Correo:</td>
                        <td><?php echo htmlspecialchars($user->getEmail()); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="action-buttons">
                <a href="<?php echo $baseUrl; ?>/auth/logout" class="btn secondary-btn">Cerrar sesión</a>
                <a href="<?php echo $baseUrl; ?>/" class="btn primary-btn">Página Principal</a>
            </div>
        </div>
    </section>
    
    <footer>
        <div class="footer-content">
            <div class="copyright">
                © 2025 Café Aroma. Todos los derechos reservados.
            </div>
            <div class="footer-links">
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
                <a href="#">Ayuda</a>
                <a href="#">Contacto</a>
            </div>
        </div>
    </footer>
</body>
</html>