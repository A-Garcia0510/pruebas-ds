<?php
/**
 * Vista del dashboard del usuario
 * 
 * Datos disponibles:
 * - $title: Título de la página
 * - $user: Objeto de usuario actual
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>

<section class="user-data-section">
    <div class="container">
        <div class="section-title">
            <h2>Mi Cuenta</h2>
        </div>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['mensaje'] ?>
                <?php unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>
        
        <p class="welcome-message">¡Bienvenido, <?= htmlspecialchars($user->getNombre()); ?>!</p>
        
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
                    <td><?= htmlspecialchars($user->getNombre()); ?></td>
                </tr>
                <tr>
                    <td>Apellidos:</td>
                    <td><?= htmlspecialchars($user->getApellidos()); ?></td>
                </tr>
                <tr>
                    <td>Correo:</td>
                    <td><?= htmlspecialchars($user->getEmail()); ?></td>
                </tr>
            </tbody>
        </table>
        
        <div class="action-buttons">
            <a href="<?= AssetHelper::url('auth/logout') ?>" class="btn secondary-btn">Cerrar sesión</a>
            <a href="<?= AssetHelper::url() ?>" class="btn primary-btn">Página Principal</a>
        </div>
    </div>
</section>