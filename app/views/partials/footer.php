<?php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>
<footer>
    <div class="footer-main">
        <div class="footer-column">
            <h3><i>🏪</i> Café-VT</h3>
            <p>Tu tienda en línea para productos de café de alta calidad</p>
        </div>
        
        <div class="footer-column">
            <h3><i>🔗</i> Enlaces rápidos</h3>
            <ul class="footer-links">
                <li><a href="<?= AssetHelper::url() ?>">Inicio</a></li>
                <li><a href="<?= AssetHelper::url('productos') ?>">Productos</a></li>
                <li><a href="<?= AssetHelper::url('servicios') ?>">Servicios</a></li>
                <li><a href="<?= AssetHelper::url('ayuda') ?>">Ayuda</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3><i>📞</i> Contacto</h3>
            <ul class="footer-links">
                <li>Email: info@cafe-vt.com</li>
                <li>Teléfono: (123) 456-7890</li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>¿Necesitas ayuda?</h3>
            <p>Estamos aquí para ayudarte con cualquier duda o problema.</p>
            <a href="<?= AssetHelper::url('ayuda') ?>" class="contact-btn">Contáctanos</a>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <div class="footer-info">
                <div>
                    <h4>Universidad</h4>
                    <ul>
                        <li><a href="#">Sobre la UCT</a></li>
                        <li><a href="#">Facultades</a></li>
                        <li><a href="#">Campus</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4>Tarjetas de Alimentación</h4>
                    <ul>
                        <li><a href="#">Comprar Tarjetas</a></li>
                        <li><a href="#">Recargar Saldo</a></li>
                    </ul>
                </div>
            </div>
            
            <div>
                <div class="social-links">
                    <a href="#"><span>IG</span></a>
                    <a href="#"><span>FB</span></a>
                    <a href="#"><span>YT</span></a>
                    <a href="#"><span>TW</span></a>
                </div>
            </div>
        </div>
    </div>
</footer>