<?php
/**
 * Vista de servicios
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('services') ?>">

<section class="hero">
    <div class="hero-content">
        <h2>Servicios de Ethos Coffee</h2>
        <p>Descubre todo lo que ofrecemos para la comunidad universitaria</p>
        <a href="#servicios" class="btn">Ver Servicios</a>
    </div>
</section>

<section class="section" id="servicios">
    <div class="container">
        <div class="section-title">
            <h2>Nuestros Servicios</h2>
        </div>
        
        <div class="info-text">
            <p>En Ethos Coffee nos enfocamos en ofrecer una experiencia gastronómica de calidad para toda la comunidad de la Universidad Católica de Temuco. Nuestros servicios están diseñados para satisfacer tus necesidades culinarias durante tu jornada académica.</p>
        </div>
        
        <div class="services-table">
            <table>
                <tr>
                    <th>Sección</th>
                    <th>Descripción</th>
                </tr>
                <tr>
                    <td>Menú Diario</td>
                    <td>Ofrecemos un menú diario variado y nutritivo, ideal para estudiantes y personal universitario. Cada plato es preparado con ingredientes frescos y de calidad.</td>
                </tr>
                <tr>
                    <td>Opciones de Pago</td>
                    <td>Para mayor comodidad, contamos con opciones de pago en línea y efectivo en el local.</td>
                </tr>
                <tr>
                    <td>Pedidos en Línea</td>
                    <td>Realiza tus pedidos a través de nuestra plataforma digital y recógelos sin esperar. Ahorra tiempo y disfruta de nuestros servicios de manera más eficiente.</td>
                </tr>
                <tr>
                    <td>Políticas de Devolución</td>
                    <td>Si tienes alguna insatisfacción con nuestros servicios, revisa nuestras políticas de devolución y garantía. Nuestro objetivo es asegurar tu satisfacción total.</td>
                </tr>
                <tr>
                    <td>Horarios de Atención</td>
                    <td>Nuestro local está abierto durante todo el horario académico para brindarte el mejor servicio en tus momentos de descanso.</td>
                </tr>
                <tr>
                    <td>Contacto y Soporte</td>
                    <td>Nuestro equipo está siempre disponible para atender tus consultas o recibir tus sugerencias. Contáctanos vía correo electrónico o directamente en nuestras instalaciones.</td>
                </tr>
            </table>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Experiencia Ethos Coffee</h2>
        </div>
        <div class="experience-content">
            <div class="experience-text">
                <h3>Más que un servicio de alimentación</h3>
                <p>En Ethos Coffee, no solo ofrecemos comida, creamos experiencias. Nuestro personal está constantemente capacitado para ofrecerte el mejor servicio con alimentos de calidad y opciones variadas.</p>
                <p>Seleccionamos los mejores ingredientes, siempre frescos y preparados con dedicación para brindarte opciones saludables y deliciosas cada día.</p>
                <p>Nuestros espacios están diseñados para que disfrutes de un ambiente agradable, ya sea para tomar un descanso entre clases o compartir con amigos.</p>
                <a href="<?= AssetHelper::url('products') ?>" class="btn">Conoce Nuestros Productos</a>
            </div>
            <div class="experience-image">
                <img src="<?= AssetHelper::img('experience.jpg') ?>" alt="Experiencia Ethos Coffee">
            </div>
        </div>
    </div>
</section> 