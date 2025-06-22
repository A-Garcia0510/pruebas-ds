<?php
/**
 * Vista principal del sistema de fidelización (Dashboard)
 */
require_once BASE_PATH . '/app/helpers/AssetHelper.php';

$user_id = $_SESSION['user_id'] ?? null;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= AssetHelper::css('loyalty') ?>">

<div class="loyalty-container">

    <!-- Header del Dashboard -->
    <div class="loyalty-dashboard-header">
        <div class="header-bg"></div>
        <div class="header-content">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <h1 id="user-name">Bienvenido, ...</h1>
                <p>Este es tu centro de comando de lealtad. ¡Sigue así!</p>
            </div>
            <div class="dashboard-card points-summary-card tier tier-dark">
                <div class="points-value" id="current-points">0</div>
                <div class="points-label">Puntos Disponibles</div>
            </div>
        </div>
    </div>

    <!-- Grid Principal del Dashboard -->
    <div class="loyalty-dashboard-grid">
        
        <!-- Columna Principal -->
        <main class="dashboard-main-col">
            <!-- Progreso de Nivel -->
            <div class="dashboard-card" id="progress-card">
                <div class="card-header">
                    <h3><i class="fas fa-flag-checkered"></i> Tu Próxima Meta</h3>
                    <span class="tier-badge" id="current-tier-badge">...</span>
                </div>
                <div class="card-body">
                    <p>Estás en camino a <strong id="next-tier-name">...</strong>. ¡Ya casi lo logras!</p>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="progress-bar-fill" style="width: 0%;"></div>
                    </div>
                    <div class="progress-bar-text">
                        <span id="progress-points-text">...</span>
                        <span id="progress-percentage-text">0%</span>
                    </div>
                </div>
            </div>

            <!-- Rangos y Beneficios -->
            <div class="dashboard-card" id="tiers-card">
                <div class="card-header">
                    <h3><i class="fas fa-crown"></i> Rangos y Beneficios</h3>
                    <span class="card-subtitle">Descubre todos los niveles disponibles</span>
                </div>
                <div class="card-body">
                    <div class="tiers-grid" id="tiers-grid">
                        <!-- Los rangos se cargarán dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Recompensas Destacadas -->
            <div class="dashboard-card" id="featured-rewards-card">
                 <div class="card-header">
                    <h3><i class="fas fa-star"></i> Recompensas Para Ti</h3>
                    <a href="<?= AssetHelper::url('/loyalty/rewards') ?>" class="card-header-link">Ver Todas <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <div class="featured-rewards-container" id="featured-rewards">
                        <p>Cargando recompensas...</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Columna Lateral -->
        <aside class="dashboard-sidebar-col">
            <!-- Beneficios -->
            <div class="dashboard-card" id="benefits-card">
                <div class="card-header">
                    <h3><i class="fas fa-gem"></i> Beneficios Actuales</h3>
                </div>
                <div class="card-body">
                    <ul class="benefits-list" id="current-benefits">
                        <li>Cargando beneficios...</li>
                    </ul>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="dashboard-card" id="actions-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <nav class="quick-actions-list">
                        <a href="<?= AssetHelper::url('/loyalty/rewards') ?>"><i class="fas fa-award"></i><span>Canjear</span></a>
                        <a href="<?= AssetHelper::url('/loyalty/profile') ?>"><i class="fas fa-user-circle"></i><span>Mi Perfil</span></a>
                        <a href="<?= AssetHelper::url('/loyalty/transactions') ?>"><i class="fas fa-history"></i><span>Historial</span></a>
                    </nav>
                </div>
            </div>
        </aside>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const api = window.loyaltyAPI;
    if (!api || !window.currentUserId) {
        console.error("Loyalty system not ready.");
        return;
    }

    function format_tier_name(tier) {
        if (!tier) return 'N/A';
        return tier.replace('cafe_', '').replace(/\b\w/g, l => l.toUpperCase());
    }

    function get_tier_class(tier) {
        if (!tier) return 'bronze';
        return tier.replace('cafe_', '');
    }

    // Datos de los rangos y sus beneficios
    const tiersData = {
        'cafe_bronze': {
            name: 'Café Bronze',
            points_required: 0,
            benefits: [
                '5% de descuento en bebidas',
                'Acceso a recompensas básicas',
                'Puntos por compra (1x)'
            ],
            icon: 'fas fa-coffee',
            color: '#cd7f32'
        },
        'cafe_plata': {
            name: 'Café Plata',
            points_required: 5000,
            benefits: [
                '10% de descuento en bebidas',
                '1 café gratis al mes',
                'Acceso prioritario',
                'Puntos por compra (1.2x)'
            ],
            icon: 'fas fa-medal',
            color: '#c0c0c0'
        },
        'cafe_oro': {
            name: 'Café Oro',
            points_required: 25000,
            benefits: [
                '15% de descuento en bebidas',
                '2 cafés gratis al mes',
                'Acceso prioritario',
                'Recompensas exclusivas',
                'Puntos por compra (1.5x)'
            ],
            icon: 'fas fa-crown',
            color: '#ffd700'
        },
        'cafe_diamante': {
            name: 'Café Diamante',
            points_required: 75000,
            benefits: [
                '20% de descuento en bebidas',
                '3 cafés gratis al mes',
                'Acceso VIP prioritario',
                'Recompensas premium',
                'Experiencias exclusivas',
                'Puntos por compra (2x)'
            ],
            icon: 'fas fa-gem',
            color: '#b9f2ff'
        }
    };

    // Función para renderizar los rangos
    function renderTiers(currentTier) {
        const tiersGrid = document.getElementById('tiers-grid');
        const tiers = ['cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante'];
        
        tiersGrid.innerHTML = tiers.map(tier => {
            const tierData = tiersData[tier];
            const isCurrentTier = tier === currentTier;
            // Un rango está desbloqueado si es el actual O si el usuario tiene suficientes puntos
            const isUnlocked = isCurrentTier || tier === 'cafe_bronze' || tierData.points_required <= (window.currentUserPoints || 0);
            
            return `
                <div class="tier-card ${isCurrentTier ? 'current-tier' : ''} ${isUnlocked ? 'unlocked' : 'locked'}" data-tier="${tier}">
                    <div class="tier-card-header">
                        <div class="tier-icon">
                            <i class="${tierData.icon}"></i>
                        </div>
                        <div class="tier-info">
                            <h4 class="tier-name">${tierData.name}</h4>
                            <p class="tier-requirements">${tierData.points_required.toLocaleString()} puntos</p>
                        </div>
                        ${isCurrentTier ? '<div class="current-tier-badge"><i class="fas fa-star"></i></div>' : ''}
                    </div>
                    <div class="tier-card-body">
                        <ul class="tier-benefits-list">
                            ${tierData.benefits.map(benefit => `
                                <li><i class="fas fa-check"></i> ${benefit}</li>
                            `).join('')}
                        </ul>
                    </div>
                    ${!isUnlocked ? '<div class="tier-locked-overlay"><i class="fas fa-lock"></i></div>' : ''}
                </div>
            `;
        }).join('');
    }

    // Función para calcular el progreso correctamente
    function calculateProgress(currentTier, currentPoints) {
        const tiers = ['cafe_bronze', 'cafe_plata', 'cafe_oro', 'cafe_diamante'];
        const currentIndex = tiers.indexOf(currentTier);
        
        if (currentIndex === -1 || currentIndex === tiers.length - 1) {
            // Si no se encuentra el tier o es el máximo, progreso 100%
            return {
                percentage: 100,
                pointsToNext: 0,
                nextTier: null
            };
        }
        
        const nextTier = tiers[currentIndex + 1];
        const nextTierData = tiersData[nextTier];
        const currentTierData = tiersData[currentTier];
        
        const pointsInCurrentTier = currentPoints - currentTierData.points_required;
        const pointsNeededForNextTier = nextTierData.points_required - currentTierData.points_required;
        
        const percentage = Math.min(100, Math.max(0, (pointsInCurrentTier / pointsNeededForNextTier) * 100));
        const pointsToNext = Math.max(0, nextTierData.points_required - currentPoints);
        
        return {
            percentage: Math.round(percentage),
            pointsToNext: pointsToNext,
            nextTier: nextTier
        };
    }

    // --- Actualizar UI ---
    function updateDashboardUI(profile) {
        // Header
        document.getElementById('user-name').textContent = `Bienvenido, ${profile.user_name || 'Usuario'}`;
        document.getElementById('current-points').textContent = (profile.current_points || 0).toLocaleString();

        // Guardar puntos para usar en renderTiers
        window.currentUserPoints = profile.current_points || 0;

        // Calcular progreso correctamente
        const progress = calculateProgress(profile.current_tier, profile.current_points || 0);
        
        // Tarjeta de Progreso
        const tierClass = get_tier_class(profile.current_tier);
        const tierName = format_tier_name(profile.current_tier);
        const nextTierName = progress.nextTier ? format_tier_name(progress.nextTier) : 'Máximo nivel alcanzado';
        
        const tierBadge = document.getElementById('current-tier-badge');
        tierBadge.textContent = tierName;
        tierBadge.className = `tier-badge tier-${tierClass}`;
        
        document.getElementById('next-tier-name').textContent = nextTierName;
        document.getElementById('progress-bar-fill').style.width = `${progress.percentage}%`;
        document.getElementById('progress-points-text').textContent = progress.pointsToNext > 0 
            ? `${progress.pointsToNext.toLocaleString()} puntos para el siguiente nivel`
            : '¡Has alcanzado el máximo nivel!';
        document.getElementById('progress-percentage-text').textContent = `${progress.percentage}%`;

        // Renderizar rangos
        renderTiers(profile.current_tier);

        // Tarjeta de Beneficios
        const benefitsList = document.getElementById('current-benefits');
        if (profile.current_benefits && profile.current_benefits.length > 0) {
            benefitsList.innerHTML = profile.current_benefits.map(b => `<li><i class="fas fa-check-circle"></i> ${b}</li>`).join('');
        } else {
            benefitsList.innerHTML = '<li><i class="fas fa-times-circle"></i> No hay beneficios en este nivel.</li>';
        }
    }

    function displayFeaturedRewards(rewards) {
        const container = document.getElementById('featured-rewards');
        if (!rewards || rewards.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No hay recompensas sugeridas por ahora.</p>';
            return;
        }

        container.innerHTML = rewards.slice(0, 2).map(reward => `
            <div class="featured-reward-item">
                <div class="reward-icon"><i class="fas fa-gift"></i></div>
                <div class="reward-details">
                    <strong>${reward.name}</strong>
                    <span>${reward.points_cost.toLocaleString()} pts</span>
                </div>
            </div>
        `).join('');
    }

    // --- Carga de datos ---
    api.getUserProfile(window.currentUserId)
        .then(response => {
            if (response && response.success) {
                updateDashboardUI(response.data);
            } else {
                throw new Error("Failed to load user profile.");
            }
        })
        .catch(err => console.error("Error loading profile:", err));

    api.getRewards()
        .then(response => {
            if (response && response.success) {
                displayFeaturedRewards(response.data);
            }
        })
        .catch(err => console.error("Error loading rewards:", err));
});
</script>
