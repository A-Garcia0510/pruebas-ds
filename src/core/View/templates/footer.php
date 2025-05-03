<?php
/**
 * Plantilla de pie de página compartida para todas las páginas
 * 
 * @var string $baseUrl URL base del proyecto, proporcionada por el controlador
 */
?>
    <div id="mensaje-confirmacion"></div>

    <footer>
        <div class="footer-main">
            <div class="footer-column">
                <h3><i>👤</i> Mi cuenta</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>/login.html">Iniciar sesión</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/registro.html">Registrarse</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/PHP/visual_datos.php">Mi perfil</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🏠</i> Nuestros Servicios</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>/Servicios.html">Catálogo de servicios</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/Ayuda.html">Ayuda</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/contacto.html">Contacto</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🛒</i> Productos</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>/PHP/productos.php">Ver productos</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/metodos-pago.html">Métodos de pago</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/envios.html">Envíos</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/devoluciones.html">Devoluciones</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>¿Necesitas ayuda?</h3>
                <p>Estamos aquí para ayudarte con cualquier duda o problema.</p>
                <a href="<?php echo $baseUrl; ?>/Ayuda.html" class="contact-btn">Contáctanos</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethos Coffe. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>