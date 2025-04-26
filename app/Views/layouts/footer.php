<footer>
        <div class="footer-main">
            <div class="footer-column">
                <h3><i>👤</i> Mi cuenta</h3>
                <ul class="footer-links">
                    <li><a href="/login">Iniciar sesión</a></li>
                    <li><a href="/registro">Registrarse</a></li>
                    <li><a href="/perfil/pedidos">Mis pedidos</a></li>
                    <li><a href="/favoritos">Mis favoritos</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🏠</i> Nuestros Locales</h3>
                <ul class="footer-links">
                    <li><a href="/locales">Encuentra tu café</a></li>
                    <li><a href="/horarios">Horarios</a></li>
                    <li><a href="/servicios">Servicios</a></li>
                    <li><a href="/eventos">Eventos</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🛒</i> Carrito</h3>
                <ul class="footer-links">
                    <li><a href="/carrito">Ver carrito</a></li>
                    <li><a href="/pagos">Métodos de pago</a></li>
                    <li><a href="/envios">Envíos</a></li>
                    <li><a href="/condiciones">Condiciones</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>¿Necesitas ayuda?</h3>
                <p>Estamos aquí para ayudarte con cualquier duda o problema.</p>
                <a href="/contacto" class="contact-btn">Contáctanos</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div>© <?php echo date('Y'); ?> Café Aroma. Todos los derechos reservados.</div>
                <div class="social-links">
                    <a href="#"><span>IG</span></a>
                    <a href="#"><span>FB</span></a>
                    <a href="#"><span>YT</span></a>
                    <a href="#"><span>TW</span></a>
                </div>
            </div>
        </div>
        
        <!-- Scripts JS -->
        <script src="/js/main.js"></script>
        <?php
        // Carga dinámica de JS específicos según la página
        $currentPage = $_SERVER['REQUEST_URI'];
        if (strpos($currentPage, 'registro') !== false) {
            echo '<script src="/js/validacion.js"></script>';
        }
        ?>
    </footer>
</body>
</html>