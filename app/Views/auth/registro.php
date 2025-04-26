<?php require_once dirname(__DIR__) . '/layouts/header.php'; ?>

<section class="registro-section">
    <div class="registro-container">
        <h2>Crear Cuenta</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form id="registroForm" action="/registro/crear" method="POST">
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
                <label for="terms">He leído y acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a> de Café Aroma.</label>
            </div>
            
            <button type="submit" class="registro-btn">Crear Cuenta</button>
            
            <div class="login-link">
                ¿Ya tienes una cuenta? <a href="/login">Inicia sesión</a>
            </div>
        </form>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/layouts/footer.php'; ?>