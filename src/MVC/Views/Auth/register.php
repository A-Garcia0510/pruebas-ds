<?php 
// src/MVC/Views/Auth/register.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Aroma - Registro</title>
    <link rel="stylesheet" href="/css/registro.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <section class="registro-section">
        <div class="registro-container">
            <h2>Crear Cuenta</h2>
            
            <?php if (isset($_SESSION['register_errors'])): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($_SESSION['register_errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['register_errors']); ?>
            <?php endif; ?>
            
            <form id="registroForm" action="/register" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" 
                           placeholder="Tu nombre" 
                           value="<?php echo isset($_SESSION['register_data']['nombre']) ? $_SESSION['register_data']['nombre'] : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control" 
                           placeholder="Tus apellidos" 
                           value="<?php echo isset($_SESSION['register_data']['apellidos']) ? $_SESSION['register_data']['apellidos'] : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" name="correo" id="correo" class="form-control" 
                           placeholder="tucorreo@ejemplo.com" 
                           value="<?php echo isset($_SESSION['register_data']['correo']) ? $_SESSION['register_data']['correo'] : ''; ?>" 
                           required>
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
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    
    <?php 
    // Limpiar datos de sesión después de mostrarlos
    if (isset($_SESSION['register_data'])) {
        unset($_SESSION['register_data']);
    }
    ?>
    
    <script src="/js/validacion.js"></script>
</body>
</html>