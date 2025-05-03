<?php
/**
 * Vista principal para la página de inicio de Ethos Coffe
 */
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>Bienvenido a Ethos Coffe</h1>
        <p>Descubre nuestra selección de café de alta calidad y accesorios para verdaderos amantes del café.</p>
        <a href="/PHP/productos.php" class="btn btn-primary">Ver Productos</a>
    </div>
</div>

<section class="features-section">
    <h2>¿Por qué elegir Ethos Coffe?</h2>
    
    <div class="features-container">
        <div class="feature-card">
            <div class="feature-icon">
                <!-- Se puede reemplazar con un ícono real -->
                <div class="icon-placeholder">☕</div>
            </div>
            <h3>Café de Especialidad</h3>
            <p>Ofrecemos café de alta calidad, cultivado de manera ética y sostenible.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <div class="icon-placeholder">🚚</div>
            </div>
            <h3>Envío Rápido</h3>
            <p>Entregamos tus productos en el menor tiempo posible para que disfrutes de tu café sin esperas.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <div class="icon-placeholder">👨‍👩‍👧‍👦</div>
            </div>
            <h3>Atención Personalizada</h3>
            <p>Nuestro equipo está siempre dispuesto a ayudarte con cualquier duda o consulta.</p>
        </div>
    </div>
</section>

<section class="featured-products">
    <h2>Productos Destacados</h2>
    
    <div class="products-container">
        <?php if(isset($featuredProducts) && !empty($featuredProducts)): ?>
            <?php foreach($featuredProducts as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if(isset($product['image'])): ?>
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                            <div class="image-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <p><?php echo $product['description']; ?></p>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <a href="/PHP/productoDetalle.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Ver Detalles</a>