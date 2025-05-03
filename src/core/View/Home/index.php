<?php
/**
 * Vista principal para la página de inicio
 */
?>
<section class="hero">
    <video id="background-video" autoplay loop muted>
        <source src="/video1.mp4" type="video/mp4">
    </video>
    <div class="hero-content">
        <h2>Bienvenidos a Ethos Coffe</h2>
        <p>Tu destino para una experiencia de compra excepcional.</p>
        <a href="/PHP/productos.php" class="btn">Ver Productos</a>
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
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-img">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="price">$<?php echo number_format($product['price'], 2); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>