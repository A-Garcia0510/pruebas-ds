<?php
/**
 * Vista de perfil del usuario en el sistema de fidelización
 * 
 * Datos disponibles:
 * - $title: Título de la página
 * - $user_profile: Perfil de fidelización del usuario
 * - $user_stats: Estadísticas detalladas del usuario
 * - $user_id: ID del usuario actual
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';

$user_id = $_SESSION['user_id'] ?? null;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= AssetHelper::css('loyalty') ?>">

<div class="loyalty-container">

    <!-- Header de Perfil -->
    <div class="profile-header-new">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="profile-info">
            <h1 id="user-name">Cargando...</h1>
            <div class="profile-tier-points">
                <span class="tier-badge" id="user-tier-badge">...</span>
                <span class="points-display"><i class="fas fa-coins"></i> <span id="user-points">0</span> pts</span>
            </div>
        </div>
    </div>

    <!-- Grid de Perfil -->
    <div class="profile-grid">

        <!-- Columna Principal (Actividad) -->
        <main class="profile-main-col">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                    <a href="<?= AssetHelper::url('/loyalty/transactions') ?>" class="card-header-link">Ver Todo <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <div class="activity-timeline" id="activity-timeline">
                        <p>Cargando actividad...</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Columna Lateral (Stats y Beneficios) -->
        <aside class="profile-sidebar-col">
            <!-- Estadísticas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Tus Estadísticas</h3>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-calendar-check"></i>
                            <span class="stat-value" id="total-visits">0</span>
                            <span class="stat-label">Visitas Totales</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-dollar-sign"></i>
                            <span class="stat-value" id="total-spent">$0</span>
                            <span class="stat-label">Gasto Total</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-award"></i>
                            <span class="stat-value" id="rewards-redeemed">0</span>
                            <span class="stat-label">Recompensas</span>
                        </div>
                         <div class="stat-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="stat-value" id="join-date">--/--/--</span>
                            <span class="stat-label">Miembro Desde</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Beneficios -->
             <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-gem"></i> Beneficios de Nivel</h3>
                </div>
                <div class="card-body">
                    <ul class="benefits-list" id="current-benefits">
                        <li>Cargando beneficios...</li>
                    </ul>
                </div>
            </div>
        </aside>

    </div>
</div>

<script src="<?= AssetHelper::js('loyalty-api') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const api = window.loyaltyAPI;
    const userId = window.currentUserId || <?= $user_id ?>;
    
    if (!userId || !api) {
        console.error("Sistema de Fidelización no está listo.");
        return;
    }

    // --- Funciones de Utilidad ---
    const formatTierName = (tier) => tier ? tier.replace('cafe_', '').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
    const getTierClass = (tier) => tier ? tier.replace('cafe_', '') : 'bronze';

    // --- Actualización de UI ---
    function updateProfileUI(profile) {
        document.getElementById('user-name').textContent = profile.user_name || 'Usuario';
        
        const tierBadge = document.getElementById('user-tier-badge');
        tierBadge.textContent = formatTierName(profile.current_tier);
        tierBadge.className = `tier-badge tier-${getTierClass(profile.current_tier)}`;
        
        document.getElementById('user-points').textContent = (profile.current_points || 0).toLocaleString();
        
        // Estadísticas
        document.getElementById('total-visits').textContent = (profile.total_visits || 0).toLocaleString();
        document.getElementById('total-spent').textContent = `$${(profile.total_spent || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('rewards-redeemed').textContent = (profile.rewards_redeemed || 0).toLocaleString();
        document.getElementById('join-date').textContent = profile.join_date ? new Date(profile.join_date).toLocaleDateString() : '--/--/--';

        // Beneficios
        const benefitsList = document.getElementById('current-benefits');
        if (profile.current_benefits && profile.current_benefits.length > 0) {
            benefitsList.innerHTML = profile.current_benefits.map(b => `<li><i class="fas fa-check-circle"></i> ${b}</li>`).join('');
        } else {
            benefitsList.innerHTML = '<li><i class="fas fa-times-circle"></i> Sin beneficios especiales.</li>';
        }
    }

    function displayActivity(transactions) {
        const timeline = document.getElementById('activity-timeline');
        if (!transactions || transactions.length === 0) {
            timeline.innerHTML = '<p class="text-center text-muted">Sin actividad reciente.</p>';
            return;
        }

        const getIcon = (type) => ({
            'earn': 'fa-plus-circle', 'redeem': 'fa-gift', 'bonus': 'fa-star', 'adjustment': 'fa-sliders-h'
        }[type] || 'fa-circle');

        timeline.innerHTML = transactions.slice(0, 5).map(t => `
            <div class="timeline-item-new">
                <div class="timeline-icon"><i class="fas ${getIcon(t.transaction_type)}"></i></div>
                <div class="timeline-details">
                    <p class="description">${t.description}</p>
                    <p class="date">${new Date(t.created_at).toLocaleString()}</p>
                </div>
                <div class="timeline-points ${t.points_amount >= 0 ? 'positive' : 'negative'}">
                    ${t.points_amount >= 0 ? '+' : ''}${t.points_amount.toLocaleString()}
                </div>
            </div>
        `).join('');
    }

    // --- Carga de Datos ---
    api.getUserProfile(userId)
        .then(res => res.success ? updateProfileUI(res.data) : console.error("Error cargando perfil"))
        .catch(err => console.error(err));
        
    // Cargar actividad reciente (primeras 5)
    api.getUserTransactions(userId, 1)
        .then(res => res.success ? displayActivity(res.data) : console.error("Error cargando transacciones"))
        .catch(err => console.error(err));
});
</script>
