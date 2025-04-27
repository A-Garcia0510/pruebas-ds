<?php 
// src/MVC/Views/Auth/dashboard.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Café Aroma - Mi Cuenta</title>
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <section class="user-data-section">
        <div class="container">
            <div class="section-title">
                <h2>Mi Cuenta</h2>
            </div>
            <p class="welcome-message">¡Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</p>
            
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
                        <td><?php echo htmlspecialchars($nombre); ?></td>
                    </tr>
                    <tr>
                        <td>Apellidos:</td>
                        <td><?php echo htmlspecialchars($apellidos); ?></td>
                    </tr>
                    <tr>
                        <td>Correo:</td>
                        <td><?php echo htmlspecialchars($correo); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="action-buttons">
                <a href="/logout" class="btn secondary-btn">Cerrar sesión</a>
                <a href="/" class="btn primary-btn">Página Principal</a>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>