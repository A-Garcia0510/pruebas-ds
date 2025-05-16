<?php
// Iniciar el monitor antes de todo
require_once __DIR__ . '/PHP/load_monitor.php';
$monitor = new LoadMonitor();

// Iniciar la sesión
session_start();

// Verificar si el usuario ya está logueado
if (isset($_SESSION['correo'])) {
    // Si está logueado, no redirigir a login
    // Puedes incluir un mensaje de bienvenida o similar si lo deseas
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casino-Express</title>
    <link rel="stylesheet" type="text/css" href="estilos/styles.css">
    <link rel="stylesheet" href="CSS/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo-container">
                <img src="Logo1.png" alt="Casino-Express">
            </div>
            <ul class="nav-links">
                <li><a href="index.php"><b>Inicio</b></a></li>
                <li><a href="Servicios.html"><b>Servicios</b></a></li>
                <li><a href="PHP/productos.php"><b>Productos</b></a></li>
                <li><a href="Ayuda.html"><b>Ayuda</b></a></li>
                <li>
                    <?php if (isset($_SESSION['correo'])): ?>
                        <a href="PHP/visual_datos.php"><b>Perfil</b></a>
                    <?php else: ?>
                        <a href="login.html"><b>Iniciar Sesión</b></a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <video id="background-video" autoplay loop muted>
            <source src="video1.mp4" type="video/mp4">
        </video>
        <div class="hero-content">
            <h2>Bienvenidos a  Ethos Coffe</h2>
            <p>Tu destino para una experiencia de compra excepcional.</p>
            <a href="PHP/productos.php" class="btn">Ver Productos</a>
        </div>
    </section>

    <section class="section" id="servicios">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Servicios</h2>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <h3>Productos de Alta Calidad</h3>
                    <p>Ofrecemos una amplia gama de productos con descripciones detalladas para que conozcas exactamente lo que estás comprando.</p>
                </div>
                
                <div class="service-card">
                    <h3>Envío Rápido</h3>
                    <p>Entendemos que quieres recibir tus productos lo antes posible. Te proporcionamos estimaciones realistas de tiempo de entrega.</p>
                </div>
                
                <div class="service-card">
                    <h3>Atención al Cliente</h3>
                    <p>Nuestro equipo está disponible para resolver todas tus dudas y preocupaciones a través de correo electrónico o teléfono.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section featured-products">
        <div class="container">
            <div class="section-title">
                <h2>Productos Destacados</h2>
            </div>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-img">
                        <img src="/api/placeholder/400/400" alt="Producto 1">
                    </div>
                    <div class="product-info">
                        <h3>Producto Premium</h3>
                        <p>Disfruta de nuestros productos de la más alta calidad.</p>
                        <div class="price">$99.99</div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="/api/placeholder/400/400" alt="Producto 2">
                    </div>
                    <div class="product-info">
                        <h3>Oferta Especial</h3>
                        <p>Aprovecha nuestras ofertas por tiempo limitado.</p>
                        <div class="price">$79.99</div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="/api/placeholder/400/400" alt="Producto 3">
                    </div>
                    <div class="product-info">
                        <h3>Recién Llegado</h3>
                        <p>Descubre nuestros productos más recientes.</p>
                        <div class="price">$129.99</div>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-img">
                        <img src="/api/placeholder/400/400" alt="Producto 4">
                    </div>
                    <div class="product-info">
                        <h3>Producto Destacado</h3>
                        <p>Uno de nuestros productos más populares.</p>
                        <div class="price">$149.99</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="mensaje-confirmacion"></div>

    <footer>
        <div class="footer-main">
            <div class="footer-column">
                <h3><i>👤</i> Mi cuenta</h3>
                <ul class="footer-links">
                    <li><a href="login.html">Iniciar sesión</a></li>
                    <li><a href="registro.html">Registrarse</a></li>
                    <li><a href="PHP/visual_datos.php">Mi perfil</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🏠</i> Nuestros Servicios</h3>
                <ul class="footer-links">
                    <li><a href="Servicios.html">Catálogo de servicios</a></li>
                    <li><a href="Ayuda.html">Ayuda</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🛒</i> Productos</h3>
                <ul class="footer-links">
                    <li><a href="PHP/productos.php">Ver productos</a></li>
                    <li><a href="#">Métodos de pago</a></li>
                    <li><a href="#">Envíos</a></li>
                    <li><a href="#">Devoluciones</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>¿Necesitas ayuda?</h3>
                <p>Estamos aquí para ayudarte con cualquier duda o problema.</p>
                <a href="Ayuda.html" class="contact-btn">Contáctanos</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Casino-Express. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="JavaScript/Java1.js"></script>
</body>
</html>
<?php
// Finalizar el monitoreo al terminar la página
$monitor->finalize(http_response_code());

// Si estamos en modo de prueba, mostrar las métricas (solo para desarrollo)
if (isset($_GET['debug_metrics']) && $_GET['debug_metrics'] === 'true') {
    echo '<div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin-top: 20px;">';
    echo '<h3>Métricas de Carga (Solo Debug)</h3>';
    echo '<pre>';
    print_r($monitor->getMetrics());
    echo '</pre>';
    echo '</div>';
}
?>