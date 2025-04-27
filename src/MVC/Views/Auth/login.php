<?php 
// src/MVC/Views/Auth/login.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Aroma - Iniciar Sesión</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <section class="login-section">
        <div class="login-container">
            <h2>Iniciar Sesión</h2>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                    ?>
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
                    <a href="/reset-password">¿Olvidaste tu contraseña?</a>
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
                ¿No tienes una cuenta? <a href="/register">Regístrate aquí</a>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>