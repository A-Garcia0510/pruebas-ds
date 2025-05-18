<?php
/**
 * Vista de registro de usuario
 * 
 * Datos disponibles:
 * - $title: Título de la página
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>

<section class="registro-section">
    <div class="registro-container">
        <h2>Crear Cuenta</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form id="registroForm" action="<?= AssetHelper::url('auth/store') ?>" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Tu nombre" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="form-control" placeholder="Tus apellidos" required>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="tucorreo@ejemplo.com" required>
            </div>
            
            <div class="form-group">
                <label for="contra">Contraseña</label>
                <input type="password" name="contra" id="contra" class="form-control" placeholder="Crea una contraseña segura" required>
            </div>
            
            <div class="terms-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">He leído y acepto los <a href="<?= AssetHelper::url('terminos') ?>">Términos y Condiciones</a> y la <a href="<?= AssetHelper::url('privacidad') ?>">Política de Privacidad</a> de Café-VT.</label>
            </div>
            
            <button type="submit" class="registro-btn">Crear Cuenta</button>
            
            <div class="login-link">
                ¿Ya tienes una cuenta? <a href="<?= AssetHelper::url('login') ?>">Inicia sesión</a>
            </div>
            
            <div class="home-link">
                <a href="<?= AssetHelper::url() ?>">Volver a la página principal</a>
            </div>
        </form>
    </div>
</section>