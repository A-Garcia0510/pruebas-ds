<?php
/**
 * Vista de recompensas del sistema de fidelización
 * 
 * Datos disponibles:
 * - $title: Título de la página
 * - $user_profile: Perfil de fidelización del usuario
 * - $rewards: Lista de recompensas disponibles
 * - $user_id: ID del usuario actual
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';

$user_id = $_SESSION['user_id'] ?? null;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= AssetHelper::css('loyalty') ?>">

<div class="loyalty-container">

    <!-- Header de Recompensas -->
    <div class="rewards-header">
        <div class="rewards-header-content">
            <div class="rewards-title-section">
                <h1 class="rewards-title"><i class="fas fa-gifts"></i> Catálogo de Recompensas</h1>
                <p class="rewards-subtitle">Usa tus puntos sabiamente. ¡Grandes premios te esperan!</p>
            </div>
            <div class="user-points-card">
                <div class="points-icon"><i class="fas fa-coins"></i></div>
                <div class="points-info">
                    <span class="points-label">Tus Puntos</span>
                    <span class="points-value" id="user-points-display">Cargando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="rewards-filters-bar">
        <div class="filter-group">
            <label for="filter-tier"><i class="fas fa-layer-group"></i> Nivel:</label>
            <select id="filter-tier" class="form-select">
                <option value="all">Todos los niveles</option>
                <option value="cafe_bronze">Bronze</option>
                <option value="cafe_plata">Plata</option>
                <option value="cafe_oro">Oro</option>
                <option value="cafe_diamante">Diamante</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-cost"><i class="fas fa-sort-amount-down"></i> Ordenar:</label>
            <select id="filter-cost" class="form-select">
                <option value="asc">Menor costo</option>
                <option value="desc">Mayor costo</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter-availability"><i class="fas fa-check-circle"></i> Disponibilidad:</label>
            <select id="filter-availability" class="form-select">
                <option value="all">Todas</option>
                <option value="available">Canjeables</option>
            </select>
        </div>
    </div>

    <!-- Grid de Recompensas -->
    <div class="rewards-grid" id="rewards-grid">
        <!-- Las recompensas se cargan aquí vía JS -->
        <div class="rewards-loader">
            <div class="spinner"></div>
            <p>Cargando recompensas...</p>
        </div>
    </div>

</div>

<!-- Template para la tarjeta de recompensa -->
<template id="reward-card-template">
    <div class="reward-card-new">
        <div class="reward-card-tier-banner"></div>
        <div class="reward-card-content">
            <div class="reward-icon-container">
                <i class="reward-icon fas fa-gift"></i>
            </div>
            <h3 class="reward-name"></h3>
            <p class="reward-description"></p>
        </div>
        <div class="reward-card-footer">
            <div class="reward-cost">
                <i class="fas fa-coins"></i>
                <span class="points"></span>
            </div>
            <button class="btn-redeem">
                <i class="fas fa-gift"></i>
                <span>Canjear</span>
            </button>
        </div>
    </div>
</template>


<script>
document.addEventListener('DOMContentLoaded', () => {
    // API y elementos del DOM
    const api = window.loyaltyAPI;
    const rewardsGrid = document.getElementById('rewards-grid');
    const userPointsDisplay = document.getElementById('user-points-display');
    const template = document.getElementById('reward-card-template');
    
    // Filtros
    const filterTier = document.getElementById('filter-tier');
    const filterCost = document.getElementById('filter-cost');
    const filterAvailability = document.getElementById('filter-availability');

    // Estado de la aplicación
    let allRewards = [];
    let userProfile = {};
    const userId = window.currentUserId || <?= $user_id ?>;

    if (!userId || !api) {
        showError('No se pudo inicializar la sección de recompensas.');
        return;
    }
    
    // --- Renderizado ---
    function renderRewards() {
        let filtered = [...allRewards];
        
        // Aplicar filtros
        if (filterTier.value !== 'all') {
            filtered = filtered.filter(r => r.tier_required === filterTier.value);
        }
        if (filterAvailability.value === 'available') {
            filtered = filtered.filter(r => (userProfile.current_points || 0) >= r.points_cost);
        }
        
        // Aplicar ordenamiento
        filtered.sort((a, b) => filterCost.value === 'asc' 
            ? a.points_cost - b.points_cost 
            : b.points_cost - a.points_cost
        );
        
        rewardsGrid.innerHTML = ''; // Limpiar grid
        
        if (filtered.length === 0) {
            showEmptyMessage();
            return;
        }

        filtered.forEach(reward => {
            const card = template.content.cloneNode(true);
            const cardElement = card.querySelector('.reward-card-new');
            
            const canAfford = (userProfile.current_points || 0) >= reward.points_cost;
            const tierClass = reward.tier_required ? reward.tier_required.replace('cafe_', '') : 'bronze';

            cardElement.classList.add(`tier-${tierClass}`);
            if (!canAfford) {
                cardElement.classList.add('disabled');
            }
            
            card.querySelector('.reward-name').textContent = reward.name;
            card.querySelector('.reward-description').textContent = reward.description || 'Sin descripción detallada.';
            card.querySelector('.reward-cost .points').textContent = `${reward.points_cost.toLocaleString()} pts`;
            
            const redeemButton = card.querySelector('.btn-redeem');
            redeemButton.dataset.rewardId = reward.id;
            redeemButton.dataset.rewardName = reward.name;
            redeemButton.dataset.rewardCost = reward.points_cost;
            redeemButton.disabled = !canAfford;

            // Configurar botón de canje
            redeemButton.querySelector('i').className = 'fas fa-gift';
            redeemButton.querySelector('span').textContent = 'Canjear';

            if (!canAfford) {
                redeemButton.querySelector('span').textContent = 'Insuficiente';
            }
            
            rewardsGrid.appendChild(card);
        });
    }

    // --- Mensajes y Carga ---
    function showLoading() {
        rewardsGrid.innerHTML = `
            <div class="rewards-loader">
                <div class="spinner"></div>
                <p>Cargando recompensas...</p>
            </div>
        `;
    }

    function showError(message) {
        rewardsGrid.innerHTML = `
            <div class="rewards-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error</h3>
                <p>${message}</p>
            </div>
        `;
    }

    function showEmptyMessage() {
        rewardsGrid.innerHTML = `
             <div class="rewards-message">
                <i class="fas fa-box-open"></i>
                <h3>Sin Resultados</h3>
                <p>No hay recompensas que coincidan con tus filtros.</p>
            </div>
        `;
    }

    // --- Lógica de Canje ---
    rewardsGrid.addEventListener('click', e => {
        const button = e.target.closest('.btn-redeem');
        if (button && !button.disabled) {
            const rewardId = button.dataset.rewardId;
            const rewardName = button.dataset.rewardName;
            const rewardCost = parseInt(button.dataset.rewardCost, 10);
            
            if (confirm(`¿Canjear "${rewardName}" por ${rewardCost.toLocaleString()} puntos?`)) {
                button.disabled = true;
                button.innerHTML = `<span>Canjeando...</span>`;

                // Hacer petición al controlador PHP para canjear recompensa
                fetch('/pruebas-ds/public/api/loyalty/redeem', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        reward_id: parseInt(rewardId, 10),
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('¡Recompensa Reclamada Gracias!');
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Error canjeando recompensa.');
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                    button.disabled = false;
                    button.innerHTML = `<span>Canjear</span>`;
                });
            }
        }
    });

    // --- Carga Inicial ---
    function initialize() {
        showLoading();
        Promise.all([
            api.getRewards(),
            api.getUserProfile(userId)
        ])
        .then(([rewardsResponse, profileResponse]) => {
            if (!rewardsResponse?.success || !profileResponse?.success) {
                throw new Error("No se pudieron cargar los datos del servidor.");
            }
            allRewards = rewardsResponse.data || [];
            userProfile = profileResponse.data || {};
            
            userPointsDisplay.textContent = (userProfile.current_points || 0).toLocaleString();
            
            renderRewards();
        })
        .catch(err => {
            console.error("Error al inicializar:", err);
            showError(err.message);
        });
    }

    // Event Listeners para filtros
    filterTier.addEventListener('change', renderRewards);
    filterCost.addEventListener('change', renderRewards);
    filterAvailability.addEventListener('change', renderRewards);

    initialize();
});
</script>
