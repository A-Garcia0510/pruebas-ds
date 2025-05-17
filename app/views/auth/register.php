<?php
/**
 * Vista para el formulario de registro
 * 
 * Variables disponibles:
 * - $error: Mensaje de error (si existe)
 * - $nombre: Valor previo del nombre
 * - $apellidos: Valor previo de apellidos
 * - $correo: Valor previo del correo
 */
?>
<section class="registro-section">
    <div class="registro-container">
        <h2>Crear Cuenta</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form id="registroForm" action="/registro" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Tu nombre" value="<?= $nombre ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="form-control" placeholder="Tus apellidos" value="<?= $apellidos ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="tucorreo@ejemplo.com" value="<?= $correo ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="contra">Contraseña</label>
                <input type="password" name="contra" id="contra" class="form-control" placeholder="Crea una contraseña segura" required>
            </div>
            
            <div class="terms-container">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">He leído y acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a> de Café Aroma.</label>
            </div>
            
            <button type="submit" class="registro-btn">Crear Cuenta</button>
            
            <div class="login-link">
                ¿Ya tienes una cuenta? <a href="/login">Inicia sesión</a>
            </div>
            
            <div class="home-link">
                <a href="/">Volver a la página principal</a>
            </div>
        </form>
    </div>
</section>