<?php
/**
 * Vista para la página de ayuda de Ethos Coffe
 */
?>

<section class="section" id="contacto">
    <div class="container">
        <div class="section-title">
            <h2>¿Cómo Podemos Ayudarte?</h2>
        </div>
        
        <div class="container-form">
            <div class="info-form">
                <h2>CONTÁCTANOS</h2>
                <p>¿Tienes alguna pregunta sobre nuestros productos o servicios? ¿Necesitas ayuda con tu pedido? Estamos aquí para asistirte con cualquier consulta que tengas.</p>
                <a href="#"><i>📞</i> +123 456 7890</a>
                <a href="#"><i>✉️</i> contacto@cafearoma.com</a>
                <a href="#"><i>📍</i> Av. Principal 123, Centro Histórico</a>
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
        document.getElementById('contact-form').addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Simple form validation
            var nombre = document.querySelector('input[name="nombre"]').value;
            var email = document.querySelector('input[name="email"]').value;
            var mensaje = document.querySelector('textarea[name="mensaje"]').value;
            
            if (nombre && email && mensaje) {
                // Form would be submitted to server here
                // For demonstration, just show success message
                document.getElementById('mensaje-confirmacion').innerHTML = '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto.';
                document.getElementById('mensaje-confirmacion').classList.add('success');
                document.getElementById('mensaje-confirmacion').style.display = 'block';
                
                // Reset form
                document.getElementById('contact-form').reset();
                
                // Hide message after 5 seconds
                setTimeout(function() {
                    document.getElementById('mensaje-confirmacion').style.display = 'none';
                }, 5000);
            }
        });
    });
</script>