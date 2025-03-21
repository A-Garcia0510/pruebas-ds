<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario ya está logueado
if (isset($_SESSION['correo'])) {
    // Si está logueado, no redirigir a login
    // Puedes incluir un mensaje de bienvenida o similar si lo deseas
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" type="text/css" href="estilos/styles.css">
  <link rel="stylesheet" type="text/css" href="estilos/barra.css">
  <title>Casino-Express</title>
</head>
<body>
  <header class="header">
    <nav>
        <ul class="nav-links">
            <li><a href=""><b>Menú</b></a></li>
                <div id="tutorialLinks">
                    <a href="http://www.w3schools.com/js/default.asp">Tutorial JavaScript</a>
                    <a href="http://www.w3schools.com/html/default.asp">Tutorial HTML</a>
                    <a href="http://www.w3schools.com/css/default.asp">Tutorial CSS</a>
                </div>
            <li><a href="Servicios.html"><b>Servicios</b></a></li>
            <li><a href="PHP/productos.php"><b>Productos</b></a></li>
            <li><a href="Ayuda.html"><b>Ayuda</b></a></li>
            <li>
                <?php if (isset($_SESSION['correo'])): ?>
                    <a href="PHP/visual_datos.php"><b>Perfil</b></a>
                <?php else: ?>
                    <a href="login.html"><b>Iniciar Sesión</b></a>
                <?php endif; ?>
            </li>
        </ul>
        <div class="logo-container">
            <img src="Logo1.png" alt="Casino-Express">
        </div>
    </nav>
</header>

<!-- Icono de servicios -->
<div id="serviciosLink"></div>

<!-- Tabla de servicios -->
  <table id="serviciosTable" class="hidden">
    <tr>
      <td>Descripciones detalladas de productos:

        1. Explora nuestros productos para descubrir sus características únicas y beneficios excepcionales. 
        Cada artículo tiene una descripción detallada que te ayudará a tomar la mejor decisión de compra.
        Precios y opciones de pago:
        
        2. Ofrecemos precios transparentes para que sepas exactamente lo que estás pagando.
        Además, tenemos opciones de pago flexibles para que elijas la que mejor se adapte a tus necesidades y conveniencia.
        Proceso de compra:
        
        3. Comprar con nosotros es fácil y rápido. Nuestro proceso de compra es simple, y estaremos contigo en cada paso del camino.
        Solo tienes que seguir nuestros sencillos pasos para asegurarte de obtener exactamente lo que deseas.
        Políticas de devolución y garantía:
        
        4. Queremos que te sientas seguro con tu compra. Por eso, ofrecemos una política de devolución sin complicaciones
        y garantías que respaldan la calidad de nuestros productos. Si no estás satisfecho, estamos aquí para ayudarte.
        Tiempo y costos de envío:
        
        5. Entendemos que quieres recibir tus productos lo antes posible. Proporcionamos estimaciones
        realistas sobre el tiempo de entrega y opciones de seguimiento para que siempre estés informado sobre el estado de tu pedido.
        Información de contacto y atención al cliente:
        
        6. ¿Tienes alguna pregunta o preocupación? Estamos aquí para ayudarte. Puedes ponerte en contacto con
        nuestro equipo de atención al cliente a través de correo electrónico o teléfono durante nuestros horarios de atención.
        Reseñas y testimonios:
        
        7. Descubre lo que dicen nuestros clientes sobre sus experiencias. Las reseñas y testimonios 
        reales son la mejor manera de conocer la calidad de nuestros productos y el nivel de servicio que ofrecemos.
        Información sobre la empresa:
        
        8. Queremos que nos conozcas. Somos una empresa comprometida con la calidad y la satisfacción del cliente. 
        Descubre nuestra historia, valores y lo que nos hace únicos en el mercado.
        FAQ (Preguntas frecuentes):
        
        9. Consulta nuestras preguntas frecuentes para obtener respuestas rápidas a las cuestiones más comunes. 
        Queremos facilitar tu experiencia de compra y resolver cualquier duda que puedas tener.</td>
    </tr>
    </table>
    <div id="mensaje-confirmacion"></div>

    <video id="background-video" autoplay loop muted>
      <source src="video1.mp4" type="video/mp4">
    </video>

  <footer>
  <p>&copy; 2024 Tienda de Casino-Express. Todos los derechos reservados.</p>
  </footer>
  <script src="JavaScript/Java1.js"></script>
  <script>
    function toggleMenu() {
      var navLinks = document.querySelector('.nav-links');
      navLinks.classList.toggle('active');
    }
  </script>
</body>
</html>
