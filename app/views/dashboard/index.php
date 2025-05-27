<?php
/**
 * Vista del dashboard del usuario
 * 
 * Datos disponibles:
 * - $title: Título de la página
 * - $user: Objeto de usuario actual
 * - $canModerate: Booleano indicando si el usuario puede moderar
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';

// Verificar si el usuario tiene permisos de moderación (variable pasada por el controlador)
// $canModerate = isset($_SESSION['rol']) && ($_SESSION['rol'] === 'Empleado' || $_SESSION['rol'] === 'Administrador'); // Lógica movida al controlador

// DEBUG: Mostrar contenido de la sesión (Eliminado)
// var_dump($_SESSION);
?>

<section class="dashboard-section">
    <div class="dashboard-container">
        <h1>Panel de Control</h1>
        <div class="user-data-section">
            <div class="section-title">
                <h2>Mi Cuenta</h2>
            </div>
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['mensaje'] ?>
                    <?php unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>
            <p class="welcome-message">¡Bienvenido, <?= htmlspecialchars($user->getNombre()); ?>!</p>
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
                        <td><?= htmlspecialchars($user->getNombre()); ?></td>
                    </tr>
                    <tr>
                        <td>Apellidos:</td>
                        <td><?= htmlspecialchars($user->getApellidos()); ?></td>
                    </tr>
                    <tr>
                        <td>Correo:</td>
                        <td><?= htmlspecialchars($user->getEmail()); ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="action-buttons">
                <a href="<?= AssetHelper::url('auth/logout') ?>" class="btn secondary-btn">Cerrar sesión</a>
                <a href="<?= AssetHelper::url() ?>" class="btn primary-btn">Página Principal</a>
            </div>
        </div>

        <?php if (isset($canModerate) && $canModerate): ?>
        <div class="dashboard-card moderation-card">
            <h2>Moderación de Reseñas</h2>
            <p>Gestiona las reseñas pendientes y reportadas.</p>
            <a href="<?= AssetHelper::url('dashboard/moderation') ?>" class="btn btn-primary">
                Ir a Moderación
            </a>
        </div>
        <?php endif; ?>
        <!-- Aquí puedes agregar más tarjetas o secciones del dashboard -->
    </div>
</section>

<style>
.dashboard-section {
    width: 100%;
    min-height: 100vh;
    background: #fcf8f3;
    padding: 2rem 0;
}
.dashboard-container {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    padding: 2rem 2rem 2rem 2rem;
}
.section-title h2 {
    margin-bottom: 1rem;
    color: #6d4c41;
}
.user-data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}
.user-data-table th, .user-data-table td {
    border: 1px solid #eee;
    padding: 0.75rem 1rem;
    text-align: left;
}
.user-data-table th {
    background: #f5f5f5;
    color: #6d4c41;
}
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}
.btn {
    display: inline-block;;
    padding: 0.5rem 1.2rem;
    border-radius: 4px;;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.primary-btn, .btn-primary {
    background: #43a047;
    color: white;
}
.primary-btn:hover, .btn-primary:hover {
    background: #388e3c;
}
.secondary-btn {
    background: #6d4c41;
    color: white;
}
.secondary-btn:hover {
    background: #4e342e;
}
.dashboard-card {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}
.moderation-card {
    border-left: 4px solid #2c3e50;
}
.moderation-card h2 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}
.moderation-card p {
    color: #666;
    margin-bottom: 1rem;
}
.welcome-message {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #4e342e;
}
.alert-success {
    background: #e8f5e9;
    color: #388e3c;
    padding: 0.75rem 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>