<?php
/**
 * Vista de ayuda
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('help') ?>">

<section class="section" id="contacto">
    <div class="container">
        <div class="section-title">
            <h2>¿Cómo Podemos Ayudarte?</h2>
        </div>
        
        <div class="container-form">
            <div class="info-form">
                <h2>CONTÁCTANOS</h2>
                <p>¿Tienes alguna pregunta sobre nuestros productos o servicios? ¿Necesitas ayuda con tu pedido? Estamos aquí para asistirte con cualquier consulta que tengas.</p>
                <a href="tel:+1234567890"><i>📞</i> +123 456 7890</a>
                <a href="mailto:contacto@ethoscoffee.com"><i>✉️</i> contacto@ethoscoffee.com</a>
                <a href="#"><i>📍</i> Universidad Católica de Temuco</a>
            </div>
            
            <form id="contact-form">
                <input type="text" name="nombre" placeholder="Nombre" class="campo" required>
                <input type="email" name="email" placeholder="Correo electrónico" class="campo" required>
                <textarea name="mensaje" placeholder="¿En qué podemos ayudarte?" required></textarea>
                <input type="submit" name="enviar" value="Enviar" class="btn-enviar">
            </form>
        </div>
        
        <div id="mensaje-confirmacion" class="mensaje-confirmacion">
            <!-- Mensaje de confirmación aparecerá aquí -->
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const mensajeConfirmacion = document.getElementById('mensaje-confirmacion');
    
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Obtener los valores del formulario
        const nombre = form.querySelector('input[name="nombre"]').value;
        const email = form.querySelector('input[name="email"]').value;
        const mensaje = form.querySelector('textarea[name="mensaje"]').value;
        
        if (nombre && email && mensaje) {
            // Aquí iría la lógica para enviar el formulario al servidor
            // Por ahora solo mostramos el mensaje de éxito
            mensajeConfirmacion.innerHTML = '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto.';
            mensajeConfirmacion.classList.add('success');
            mensajeConfirmacion.style.display = 'block';
            
            // Resetear el formulario
            form.reset();
            
            // Ocultar el mensaje después de 5 segundos
            setTimeout(function() {
                mensajeConfirmacion.style.display = 'none';
            }, 5000);
        }
    });
});
</script> 