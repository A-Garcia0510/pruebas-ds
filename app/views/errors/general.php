<?php
/**
 * Vista para mensajes de error generales
 * 
 * Variables disponibles:
 * - $message: Mensaje de error
 */
?>
<section class="error-section">
    <div class="container">
        <div class="section-title">
            <h2>Error</h2>
        </div>
        <div class="error-content">
            <p><?= $message ?? 'Ha ocurrido un error desconocido.' ?></p>
            <div class="action-buttons">
                <a href="/" class="btn primary-btn">Volver al inicio</a>
            </div>
        </div>
    </div>
</section>