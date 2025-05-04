<?php
/**
 * Plantilla de encabezado compartida para todas las páginas
 * 
 * @var string $baseUrl URL base del proyecto, proporcionada por el controlador
 * @var string $title Título de la página, proporcionado por el controlador
 * @var bool $isLoggedIn Indica si el usuario está autenticado
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Ethos Coffe'; ?></title>
    
    <!-- Cargar los estilos con la URL base correcta -->
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl; ?>/CSS/index.css">
    
    <!-- Debug CSS - Solo para desarrollo -->
    <style>
        .debug-info {
            position: fixed;
            bottom: 0;
            right: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            font-size: 12px;
            z-index: 9999;
            max-width: 300px;
            overflow: auto;
            max-height: 200px;
        }
        .debug-info h4 {
            margin: 0 0 5px 0;
        }
        .debug-info p {
            margin: 0 0 3px 0;
        }
    </style>
    
    <!-- jQuery (si lo necesitas) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo-container">
                <a href="<?php echo $baseUrl; ?>/">
                    <img src="<?php echo $baseUrl; ?>/Logo1.png" alt="Ethos Coffe">
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo $baseUrl; ?>/"><b>Inicio</b></a></li>
                <li><a href="<?php echo $baseUrl; ?>/servicios"><b>Servicios</b></a></li>
                <li><a href="<?php echo $baseUrl; ?>/PHP/productos.php"><b>Productos</b></a></li>
                <li><a href="<?php echo $baseUrl; ?>/ayuda"><b>Ayuda</b></a></li>
                <li>
                    <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                        <a href="<?php echo $baseUrl; ?>/PHP/visual_datos.php"><b>Perfil</b></a>
                    <?php else: ?>
                        <a href="<?php echo $baseUrl; ?>/login.html"><b>Iniciar Sesión</b></a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>
    
    <!-- Debug info - Solo para desarrollo -->
    <?php if(isset($debug) && $debug): ?>
    <div class="debug-info">
        <h4>Debug Info</h4>
        <p><strong>Base URL:</strong> <?php echo $baseUrl; ?></p>
        <p><strong>CSS Path:</strong> <?php echo $baseUrl; ?>/CSS/index.css</p>
        <p><strong>Script Name:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
    </div>
    <?php endif; ?>