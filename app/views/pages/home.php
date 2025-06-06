<?php
/**
 * Vista de la página de inicio
 * 
 * Datos disponibles:
 * - $isLoggedIn: Indica si el usuario está logueado
 * - $featuredProducts: Array de productos destacados
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('index') ?>">

<section class="hero">
    <video autoplay muted loop class="hero-video">
        <source src="<?= AssetHelper::url('videos/video1.mp4') ?>" type="video/mp4">
        Tu navegador no soporta el elemento de video.
    </video>
    <div class="hero-content">
        <h2>Bienvenidos a Ethos Coffee</h2>
        <p>Tu destino para una experiencia de compra excepcional.</p>
        <a href="<?= AssetHelper::url('products') ?>" class="btn">Ver Productos</a>
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
            <?php if (!empty($featuredProducts)): ?>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card">
                        <div class="product-image">
                            <?php
                            $nombre_imagen = strtolower(str_replace(' ', '_', $product->getName())) . '.jpg';
                            $imagen_ruta = "IMG-P/" . $nombre_imagen;
                            if (file_exists(BASE_PATH . '/public/' . $imagen_ruta)) {
                                echo '<img src="' . AssetHelper::url($imagen_ruta) . '" alt="' . htmlspecialchars($product->getName()) . '">';
                            } else {
                                echo '<img src="/api/placeholder/400/400" alt="' . htmlspecialchars($product->getName()) . '">';
                            }
                            ?>
                    </div>
                    <div class="product-info">
                            <h3><?= htmlspecialchars($product->getName()) ?></h3>
                            <p class="price">$<?= number_format($product->getPrice(), 0, ',', '.') ?></p>
                            <a href="<?= AssetHelper::url('products/detail/' . $product->getId()) ?>" class="btn">Ver Detalles</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
                <p class="no-products">No hay productos destacados disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<div id="mensaje-confirmacion"></div>