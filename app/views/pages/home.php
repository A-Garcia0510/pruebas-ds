<?php
// Rastreador de errores para verificar el flujo de renderizado
if (!defined('RENDER_DEBUG') && (isset($_GET['debug']) || (isset($config['app']['debug']) && $config['app']['debug']))) {
    define('RENDER_DEBUG', true);
    function debug_log($message) {
        error_log($message);
        if (isset($_GET['debug_display'])) {
            echo "<!-- DEBUG: " . htmlspecialchars($message) . " -->\n";
        }
    }
} else {
    if (!defined('RENDER_DEBUG')) define('RENDER_DEBUG', false);
    function debug_log($message) { /* No hacer nada */ }
}

/**
 * Vista de la página de inicio
 * 
 * Datos disponibles:
 * - $isLoggedIn: Indica si el usuario está logueado
 * - $featuredProducts: Array de productos destacados
 */
debug_log('Renderizando vista home.php');
?>

<section class="hero">
    <?php debug_log('Inicio de sección hero'); ?>
    <div class="hero-content">
        <h2>Bienvenidos a Ethos Coffee</h2>
        <p>Tu destino para una experiencia de compra excepcional.</p>
        <a href="<?= $config['app']['url'] ?>/productos" class="btn">Ver Productos</a>
    </div>
    <?php debug_log('Fin de sección hero'); ?>
</section>

<section class="section" id="servicios">
    <?php debug_log('Inicio de sección servicios'); ?>
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
    <?php debug_log('Fin de sección servicios'); ?>
</section>

<section class="section featured-products">
    <?php debug_log('Inicio de sección productos destacados'); ?>
    <div class="container">
        <div class="section-title">
            <h2>Productos Destacados</h2>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="product-img">
                        <img src="<?= $config['app']['url'] . $product['image'] ?>" alt="<?= $product['name'] ?>">
                    </div>
                    <div class="product-info">
                        <h3><?= $product['name'] ?></h3>
                        <p><?= $product['description'] ?></p>
                        <div class="price">$<?= number_format($product['price'], 2) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php debug_log('Fin de sección productos destacados'); ?>
</section>

<div id="mensaje-confirmacion"></div>
<?php debug_log('Fin de renderizado de home.php'); ?>