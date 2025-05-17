<?php
/**
 * Vista para el formulario de inicio de sesión
 * 
 * Variables disponibles:
 * - $error: Mensaje de error (si existe)
 */
?>
<section class="login-section">
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form action="/login" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-control" placeholder="tucorreo@ejemplo.com" required>
            </div>
            
            <div class="form-group">
                <label for="contra">Contraseña</label>
                <input type="password" name="contra" id="contra" class="form-control" placeholder="Tu contraseña" required>
            </div>
            
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit" class="login-btn">Iniciar Sesión</button>
        </form>
        
        <div class="login-divider">
            <span>O CONÉCTATE CON</span>
        </div>
        
        <div class="social-login">
            <a href="#" class="social-btn" title="Facebook">FB</a>
            <a href="#" class="social-btn" title="Google">G</a>
            <a href="#" class="social-btn" title="Apple">A</a>
        </div>
        
        <div class="register-link">
            ¿No tienes una cuenta? <a href="/registro">Regístrate aquí</a>
        </div>
    </div>
</section>