<?php require_once dirname(__DIR__) . '/layouts/header.php'; ?>

<!-- Banner Principal -->
<section class="hero-section">
    <div class="hero-content">
        <h1>Bienvenido a Café Aroma</h1>
        <p>Descubre el sabor auténtico de nuestros cafés especiales</p>
        <a href="/productos" class="btn-primary">Ver Menú</a>
    </div>
</section>

<!-- Categorías destacadas -->
<section class="categories-section">
    <div class="container">
        <h2>Nuestras Especialidades</h2>
        
        <div class="categories-grid">
            <div class="category-card">
                <div class="category-img">
                    <img src="/img/cafe-espresso.jpg" alt="Espresso">
                </div>
                <h3>Espresso</h3>
                <p>Nuestras mezclas premium para los amantes del café intenso.</p>
                <a href="/categoria/espresso" class="btn-secondary">Explorar</a>
            </div>
            
            <div class="category-card">
                <div class="category-img">
                    <img src="/img/cafe-frio.jpg" alt="Café Frío">
                </div>
                <h3>Bebidas Frías</h3>
                <p>Refrescantes opciones para los días calurosos.</p>
                <a href="/categoria/frio" class="btn-secondary">Explorar</a>
            </div>
            
            <div class="category-card">
                <div class="category-img">
                    <img src="/img/postres.jpg" alt="Postres">
                </div>
                <h3>Postres</h3>
                <p>Delicias dulces para acompañar tu bebida favorita.</p>
                <a href="/categoria/postres" class="btn-secondary">Explorar</a>
            </div>
            
            <div class="category-card">
                <div class="category-img">
                    <img src="/img/desayunos.jpg" alt="Desayunos">
                </div>
                <h3>Desayunos</h3>
                <p>Inicia tu día con nuestras opciones nutritivas y deliciosas.</p>
                <a href="/categoria/desayunos" class="btn-secondary">Explorar</a>
            </div>
        </div>
    </div>
</section>

<!-- Productos destacados -->
<section class="featured-products">
    <div class="container">
        <h2>Los Favoritos de Nuestros Clientes</h2>
        
        <div class="products-slider">
            <?php if (isset($featuredProducts) && !empty($featuredProducts)): ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-img">
                            <img src="<?php echo $product['imagen']; ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                            <?php if ($product['es_nuevo']): ?>
                                <span class="badge new">Nuevo</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['nombre']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['descripcion_corta']); ?></p>
                            <div class="product-footer">
                                <span class="price">$<?php echo number_format($product['precio'], 2); ?></span>
                                <a href="/producto/<?php echo $product['id']; ?>" class="btn-add-cart">Agregar</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay productos destacados disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Sobre Nosotros -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>Nuestra Historia</h2>
                <p>En Café Aroma, nos dedicamos a ofrecer la mejor experiencia de café desde 2010. Seleccionamos cuidadosamente los granos de las mejores regiones productoras para garantizar un sabor excepcional en cada taza.</p>
                <p>Nuestro compromiso con la calidad y el servicio nos ha convertido en el lugar preferido para los amantes del café en la ciudad.</p>
                <a href="/nosotros" class="btn-text">Conoce más sobre nosotros</a>
            </div>
            <div class="about-image">
                <img src="/img/cafe-shop.jpg" alt="Nuestra Cafetería">
            </div>
        </div>
    </div>
</section>

<!-- Testimonios -->
<section class="testimonials-section">
    <div class="container">
        <h2>Lo que dicen nuestros clientes</h2>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="rating">★★★★★</div>
                <p>"El mejor café que he probado en la ciudad. El ambiente es acogedor y el personal muy amable."</p>
                <div class="testimonial-author">- Ana García</div>
            </div>
            
            <div class="testimonial-card">
                <div class="rating">★★★★★</div>
                <p>"Me encanta venir a trabajar aquí. Tienen buen WiFi y el café siempre está perfecto."</p>
                <div class="testimonial-author">- Carlos Rodríguez</div>
            </div>
            
            <div class="testimonial-card">
                <div class="rating">★★★★☆</div>
                <p>"Los postres son increíbles y combinan perfectamente con su selección de cafés. ¡Volveré pronto!"</p>
                <div class="testimonial-author">- Laura Martínez</div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <h2>Suscríbete a nuestro boletín</h2>
            <p>Recibe nuestras promociones y novedades directamente en tu correo electrónico.</p>
            
            <form action="/newsletter/suscribir" method="POST" class="newsletter-form">
                <input type="email" name="email" placeholder="Tu correo electrónico" required>
                <button type="submit" class="btn-submit">Suscribirse</button>
            </form>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>