<?php
// src/core/View/auth/register.php
$baseUrl = $this->router->getBaseUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Aroma - Registro</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/CSS/registro.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Café<span>Aroma</span></h1>
            </div>
            <ul class="main-menu">
                <li><a href="<?php echo $baseUrl; ?>/">Inicio</a></li>
                <li><a href="<?php echo $baseUrl; ?>/PHP/productos.php">Menú</a></li>
                <li><a href="<?php echo $baseUrl; ?>/servicios">Sobre Nosotros</a></li>
            </ul>
            <div class="user-actions">
                <div class="icon">👤</div>
                <div class="icon">❤️</div>
                <div class="icon">🛒</div>
            </div>
        </nav>
    </header>
    
    <section class="registro-section">
        <div class="registro-container">
            <h2>Crear Cuenta</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form id="registroForm" action="<?php echo $baseUrl; ?>/auth/store" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" 
                           placeholder="Tu nombre" required
                           value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" class="form-control" 
                           placeholder="Tus apellidos" required
                           value="<?php echo isset($apellidos) ? htmlspecialchars($apellidos) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" name="correo" id="correo" class="form-control" 
                           placeholder="tucorreo@ejemplo.com" required
                           value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="contra">Contraseña</label>
                    <input type="password" name="contra" id="contra" class="form-control" 
                           placeholder="Crea una contraseña segura" required>
                </div>
                
                <div class="terms-container">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">He leído y acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a> de Café Aroma.</label>
                </div>
                
                <button type="submit" class="registro-btn">Crear Cuenta</button>
                
                <div class="login-link">
                    ¿Ya tienes una cuenta? <a href="<?php echo $baseUrl; ?>/auth/login">Inicia sesión</a>
                </div>
                
                <div class="home-link">
                    <a href="<?php echo $baseUrl; ?>/">Volver a la página principal</a>
                </div>
            </form>
        </div>
    </section>
    
    <footer>
        <div class="footer-main">
            <div class="footer-column">
                <h3><i>👤</i> Mi cuenta</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo $baseUrl; ?>/auth/login">Iniciar sesión</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/auth/register">Registrarse</a></li>
                    <li><a href="#">Mis pedidos</a></li>
                    <li><a href="#">Mis favoritos</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🏠</i> Nuestros Locales</h3>
                <ul class="footer-links">
                    <li><a href="#">Encuentra tu café</a></li>
                    <li><a href="#">Horarios</a></li>
                    <li><a href="#">Servicios</a></li>
                    <li><a href="#">Eventos</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3><i>🛒</i> Carrito</h3>
                <ul class="footer-links">
                    <li><a href="#">Ver carrito</a></li>
                    <li><a href="#">Métodos de pago</a></li>
                    <li><a href="#">Envíos</a></li>
                    <li><a href="#">Condiciones</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>¿Necesitas ayuda?</h3>
                <p>Estamos aquí para ayudarte con cualquier duda o problema.</p>
                <a href="#" class="contact-btn">Contáctanos</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div>© 2025 Café Aroma. Todos los derechos reservados.</div>
                <div class="social-links">
                    <a href="#"><span>IG</span></a>
                    <a href="#"><span>FB</span></a>
                    <a href="#"><span>YT</span></a>
                    <a href="#"><span>TW</span></a>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo $baseUrl; ?>/JavaScript/validacion.js"></script>
</body>
</html>