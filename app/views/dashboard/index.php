<?php
/**
 * Vista para el dashboard del usuario
 * 
 * Variables disponibles:
 * - $nombre: Nombre del usuario
 * - $apellidos: Apellidos del usuario
 * - $correo: Correo electrónico del usuario
 */
?>
<section class="user-data-section">
    <div class="container">
        <div class="section-title">
            <h2>Mi Cuenta</h2>
        </div>
        <p class="welcome-message">¡Bienvenido, <?= htmlspecialchars($nombre); ?>!</p>
        
        <table class="user-data-table">
            <thead>
                <tr>
                    <th>Datos</th>
                    <th>Información</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Nombre:</td>
                    <td><?= htmlspecialchars($nombre); ?></td>
                </tr>
                <tr>
                    <td>Apellidos:</td>
                    <td><?= htmlspecialchars($apellidos); ?></td>
                </tr>
                <tr>
                    <td>Correo:</td>
                    <td><?= htmlspecialchars($correo); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="action-buttons">
            <a href="/logout" class="btn secondary-btn">Cerrar sesión</a>
            <a href="/" class="btn primary-btn">Página Principal</a>
        </div>
    </div>
</section>