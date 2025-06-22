/**
 * Componentes JavaScript para el Sistema de Fidelización
 */

/**
 * Dashboard de Fidelización
 */
class LoyaltyDashboard {
    constructor(api, userId) {
        this.api = api;
        this.userId = userId;
    }
    
    /**
     * Cargar dashboard completo
     */
    async loadDashboard(userId = null) {
        const targetUserId = userId || this.userId;
        if (!targetUserId) {
            console.error('No se pudo obtener el ID del usuario');
            return;
        }
        
        try {
            const profile = await this.api.getUserProfile(targetUserId);
            this.updateDashboard(profile);
        } catch (error) {
            this.api.handleError(error);
        }
    }
    
    /**
     * Actualizar dashboard con datos del perfil
     */
    updateDashboard(profile) {
        // Actualizar puntos
        const pointsElement = document.querySelector('.points-number');
        if (pointsElement) {
            pointsElement.textContent = profile.current_points?.toLocaleString() || '0';
        }
        
        // Actualizar nivel
        const tierElement = document.querySelector('.tier-name');
        if (tierElement) {
            tierElement.textContent = this.formatTierName(profile.current_tier);
        }
        
        // Actualizar progreso
        const progressElement = document.querySelector('.progress-fill');
        if (progressElement) {
            const percentage = profile.progress_percentage || 0;
            progressElement.style.width = `${percentage}%`;
        }
        
        // Actualizar texto de progreso
        const progressText = document.querySelector('.progress-text');
        if (progressText) {
            progressText.textContent = `${profile.progress_percentage || 0}% al siguiente nivel`;
        }
        
        // Actualizar puntos totales
        const totalPoints = document.querySelector('.total-number');
        if (totalPoints) {
            totalPoints.textContent = profile.total_points?.toLocaleString() || '0';
        }
    }
    
    /**
     * Cargar recompensas destacadas
     */
    async loadFeaturedRewards() {
        try {
            const response = await this.api.getRewards({ featured: true, limit: 4 });
            if (response && response.success) {
                this.displayFeaturedRewards(response.data);
            }
        } catch (error) {
            this.api.handleError(error);
        }
    }
    
    /**
     * Mostrar recompensas destacadas
     */
    displayFeaturedRewards(rewards) {
        const container = document.getElementById('featured-rewards');
        if (!container) return;
        
        if (!rewards || rewards.length === 0) {
            container.innerHTML = '<p class="no-rewards">No hay recompensas destacadas disponibles</p>';
            return;
        }
        
        container.innerHTML = rewards.map(reward => `
            <div class="reward-card featured">
                <div class="reward-image">
                    <img src="${reward.image || '/public/img/default-reward.jpg'}" alt="${reward.name}">
                </div>
                <div class="reward-info">
                    <h4>${reward.name}</h4>
                    <p>${reward.description}</p>
                    <div class="reward-cost">
                        <i class="fas fa-coins"></i>
                        <span>${reward.points_cost} puntos</span>
                    </div>
                </div>
                <div class="reward-actions">
                    <button class="btn btn-primary" onclick="redeemReward(${reward.id})">
                        Canjear
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Cargar transacciones recientes
     */
    async loadRecentTransactions() {
        if (!this.userId) return; // No hacer nada si no hay ID de usuario
        try {
            const response = await this.api.getUserTransactions(this.userId, 1, 5);
            if (response && response.success) {
                this.displayRecentTransactions(response.data);
            }
        } catch (error) {
            this.api.handleError(error);
        }
    }
    
    /**
     * Mostrar transacciones recientes
     */
    displayRecentTransactions(transactions) {
        const container = document.getElementById('recent-transactions');
        if (!container) return;
        
        if (!transactions || transactions.length === 0) {
            container.innerHTML = '<p class="no-transactions">No hay transacciones recientes</p>';
            return;
        }
        
        container.innerHTML = transactions.map(transaction => `
            <div class="transaction-item recent">
                <div class="transaction-icon">
                    <i class="${this.getTransactionIcon(transaction.transaction_type)}"></i>
                </div>
                <div class="transaction-details">
                    <div class="transaction-type">${this.getTransactionTypeText(transaction.transaction_type)}</div>
                    <div class="transaction-description">${transaction.description}</div>
                    <div class="transaction-date">${this.formatDate(transaction.created_at)}</div>
                </div>
                <div class="transaction-amount ${transaction.points_amount > 0 ? 'positive' : 'negative'}">
                    ${transaction.points_amount > 0 ? '+' : ''}${transaction.points_amount} puntos
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Formatear nombre del nivel
     */
    formatTierName(tier) {
        const tierNames = {
            'cafe_bronze': 'Café Bronze',
            'cafe_silver': 'Café Silver',
            'cafe_gold': 'Café Gold',
            'cafe_diamond': 'Café Diamond'
        };
        return tierNames[tier] || tier;
    }
    
    /**
     * Obtener icono de transacción
     */
    getTransactionIcon(type) {
        const icons = {
            'earn': 'fas fa-plus-circle',
            'redeem': 'fas fa-gift',
            'referral': 'fas fa-users',
            'expire': 'fas fa-clock',
            'bonus': 'fas fa-star'
        };
        return icons[type] || 'fas fa-circle';
    }
    
    /**
     * Obtener texto de tipo de transacción
     */
    getTransactionTypeText(type) {
        const types = {
            'earn': 'Ganancia de puntos',
            'redeem': 'Canje de recompensa',
            'referral': 'Puntos por referido',
            'expire': 'Puntos expirados',
            'bonus': 'Bono especial'
        };
        return types[type] || 'Transacción';
    }
    
    /**
     * Formatear fecha
     */
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

/**
 * Catálogo de Recompensas
 */
class RewardsCatalog {
    constructor(api, userId) {
        this.api = api;
        this.userId = userId;
        this.currentFilters = {};
        this.currentPage = 1;
        this.loading = false;
    }
    
    /**
     * Cargar recompensas
     */
    async loadRewards() {
        if (this.loading) return;
        
        this.loading = true;
        this.showLoading();
        
        try {
            const response = await this.api.getRewards(this.currentFilters);
            if (response && response.success) {
                this.displayRewards(response.data);
            } else {
                this.displayRewards([]); // Mostrar vacío si no hay éxito
            }
        } catch (error) {
            this.api.handleError(error);
            this.showError();
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }
    
    /**
     * Mostrar recompensas
     */
    displayRewards(rewards) {
        const container = document.getElementById('rewards-grid');
        if (!container) return;
        
        if (!rewards || rewards.length === 0) {
            this.showNoRewards();
            return;
        }
        
        container.innerHTML = rewards.map(reward => `
            <div class="reward-card" data-tier="${reward.tier_required}">
                <div class="reward-image">
                    <img src="${reward.image || '/public/img/default-reward.jpg'}" alt="${reward.name}">
                    ${reward.tier_required ? `<div class="tier-badge ${reward.tier_required}">${this.formatTierName(reward.tier_required)}</div>` : ''}
                </div>
                <div class="reward-info">
                    <h4>${reward.name}</h4>
                    <p>${reward.description}</p>
                    <div class="reward-details">
                        <div class="reward-cost">
                            <i class="fas fa-coins"></i>
                            <span>${reward.points_cost} puntos</span>
                        </div>
                        ${reward.discount_percent ? `<div class="reward-discount">${reward.discount_percent}% descuento</div>` : ''}
                    </div>
                </div>
                <div class="reward-actions">
                    <button class="btn btn-primary" onclick="openRedeemModal(${JSON.stringify(reward).replace(/"/g, '&quot;')})">
                        <i class="fas fa-gift"></i> Canjear
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Aplicar filtros
     */
    applyFilters() {
        this.currentFilters = {
            tier: document.getElementById('tier-filter')?.value || '',
            category: document.getElementById('category-filter')?.value || '',
            sort: document.getElementById('points-filter')?.value || ''
        };
        
        this.currentPage = 1;
        this.loadRewards();
    }
    
    /**
     * Mostrar loading
     */
    showLoading() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.style.display = 'flex';
        }
    }
    
    /**
     * Ocultar loading
     */
    hideLoading() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.style.display = 'none';
        }
    }
    
    /**
     * Mostrar error
     */
    showError() {
        const container = document.getElementById('rewards-grid');
        if (container) {
            container.innerHTML = '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error cargando recompensas</div>';
        }
    }
    
    /**
     * Mostrar no hay recompensas
     */
    showNoRewards() {
        const container = document.getElementById('rewards-grid');
        const noRewards = document.getElementById('no-rewards');
        
        if (container) {
            container.innerHTML = '';
        }
        
        if (noRewards) {
            noRewards.style.display = 'block';
        }
    }
    
    /**
     * Formatear nombre del nivel
     */
    formatTierName(tier) {
        const tierNames = {
            'cafe_bronze': 'Bronze',
            'cafe_silver': 'Silver',
            'cafe_gold': 'Gold',
            'cafe_diamond': 'Diamond'
        };
        return tierNames[tier] || tier;
    }
}

/**
 * Sistema de Referidos
 */
class ReferralSystem {
    constructor(api, userId) {
        this.api = api;
        this.userId = userId;
        this.elements = {
            generateButton: document.getElementById('generate-referral-code'),
            useButton: document.getElementById('use-referral-code'),
            codeInput: document.getElementById('referral-code-input'),
            displayCode: document.getElementById('my-referral-code'),
            referralsList: document.getElementById('referrals-list'),
            feedback: document.getElementById('referral-feedback'),
        };
        this.init();
    }
    
    init() {
        if (!this.userId) return;
        this.generateButton.addEventListener('click', () => this.generateCode());
        this.useButton.addEventListener('click', () => this.useCode(this.codeInput.value));
    }
    
    async generateCode() {
        if (!this.userId) return;
        try {
            const response = await this.api.generateReferral(this.userId);
            if (response && response.success) {
                this.updateReferralDisplay({ my_code: response.data.referral_code });
                this.showFeedback('¡Código generado con éxito!', 'success');
            }
        } catch (error) {
            this.api.handleError(error);
            this.showFeedback('Error al generar el código.', 'error');
        }
    }

    async useCode(code) {
        if (!this.userId) return;
        if (!code) {
            this.showFeedback('Por favor, ingresa un código.', 'error');
            return;
        }
        
        try {
            const response = await this.api.useReferral(this.userId, code);
            if (response && response.success) {
                this.showFeedback('¡Código canjeado con éxito! Ganaste puntos.', 'success');
                this.loadReferralData(); // Recargar datos
            }
        } catch (error) {
            this.api.handleError(error);
            this.showFeedback(error.message || 'El código no es válido o ya fue usado.', 'error');
        }
    }

    async loadReferralData() {
        if (!this.userId) return;
        try {
            const response = await this.api.getReferralData(this.userId);
            if (response && response.success) {
                this.updateReferralDisplay(response.data);
            }
        } catch (error) {
            this.api.handleError(error);
        }
    }

    updateReferralDisplay(data) {
        // Actualizar código de referido
        const codeElement = document.querySelector('.referral-code');
        if (codeElement && data.my_code) {
            codeElement.textContent = data.my_code;
        }
        
        // Actualizar estadísticas
        const statsElements = document.querySelectorAll('.stat-number');
        if (statsElements.length >= 4) {
            statsElements[0].textContent = data.total_referrals || 0;
            statsElements[1].textContent = data.active_referrals || 0;
            statsElements[2].textContent = (data.total_points_earned || 0).toLocaleString();
            statsElements[3].textContent = (data.conversion_rate || 0) + '%';
        }
    }

    showFeedback(message, type) {
        const feedback = this.elements.feedback;
        feedback.textContent = message;
        feedback.className = `feedback feedback-${type}`;
        setTimeout(() => {
            feedback.textContent = '';
            feedback.className = 'feedback';
        }, 3000);
    }
}

/**
 * Seguimiento de Puntos
 */
class PointsTracker {
    constructor(api, userId) {
        this.api = api;
        this.userId = userId;
        this.currentPoints = 0;
        this.elements = {
            pointsDisplay: document.querySelector('.user-points-dynamic'),
        };
    }
    
    async updatePoints() {
        if (!this.userId) return;
        try {
            const profile = await this.api.getUserProfile(this.userId);
            if (profile) {
                this.currentPoints = profile.current_points || 0;
                this.updatePointsDisplay();
            }
        } catch (error) {
            this.api.handleError(error);
        }
    }
    
    updatePointsDisplay() {
        const pointsElements = document.querySelectorAll('.points-number, .current-points');
        pointsElements.forEach(element => {
            element.textContent = this.currentPoints.toLocaleString();
        });
    }
    
    animatePointsEarned(points) {
        const pointsElement = document.querySelector('.points-number');
        if (!pointsElement) return;
        
        const currentPoints = parseInt(pointsElement.textContent.replace(/,/g, ''));
        const newPoints = currentPoints + points;
        
        // Animación de contador
        let current = currentPoints;
        const increment = points / 20; // 20 pasos
        const timer = setInterval(() => {
            current += increment;
            if (current >= newPoints) {
                current = newPoints;
                clearInterval(timer);
            }
            pointsElement.textContent = Math.floor(current).toLocaleString();
        }, 50);
        
        // Efecto visual
        pointsElement.style.transform = 'scale(1.2)';
        pointsElement.style.color = '#28a745';
        
        setTimeout(() => {
            pointsElement.style.transform = 'scale(1)';
            pointsElement.style.color = '';
        }, 1000);
    }
    
    async checkTierUpgrade() {
        if (!this.userId) return;
        try {
            const response = await this.api.checkTierUpgrade(this.userId);
            if (response && response.success && response.data.tier_upgraded) {
                this.showTierUpgradeNotification(response.data.new_tier);
            }
        } catch (error) {
            this.api.handleError(error);
        }
    }
    
    showTierUpgradeNotification(newTier) {
        const tierNames = {
            'cafe_bronze': 'Café Bronze',
            'cafe_silver': 'Café Silver',
            'cafe_gold': 'Café Gold',
            'cafe_diamond': 'Café Diamond'
        };
        
        const tierName = tierNames[newTier] || newTier;
        
        // Crear notificación especial
        const notification = document.createElement('div');
        notification.className = 'notification notification-success tier-upgrade';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-crown"></i>
                <div>
                    <h4>¡Felicidades!</h4>
                    <p>Has subido al nivel ${tierName}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Remover después de 10 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);
    }
}

// Funciones globales para uso en HTML
window.redeemReward = function(reward) {
    if (typeof reward === 'string') {
        reward = JSON.parse(reward);
    }
    openRedeemModal(reward);
};

window.openRedeemModal = function(reward) {
    // Esta función debe estar definida en el HTML de la página de recompensas
    if (typeof window.openRedeemModal === 'function') {
        window.openRedeemModal(reward);
    }
};

// Inicialización de los componentes en las vistas correspondientes.
// Esto debe hacerse en cada vista de PHP donde se necesiten.

// Ejemplo para la vista de dashboard de fidelización (loyalty/index.php)
document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.currentUserId !== 'undefined' && window.currentUserId !== null) {
        // Usar la instancia global de la API
        const api = window.loyaltyAPI || new (window.LoyaltyAPI || function() {});
        
        if (document.querySelector('.loyalty-dashboard')) {
            const dashboard = new LoyaltyDashboard(api, window.currentUserId);
            dashboard.loadDashboard();
            dashboard.loadFeaturedRewards();
            dashboard.loadRecentTransactions();
        }

        if (document.querySelector('.rewards-catalog')) {
            const catalog = new RewardsCatalog(api, window.currentUserId);
            catalog.loadRewards();
        }

        if (document.querySelector('.referral-system')) {
            const referrals = new ReferralSystem(api, window.currentUserId);
            referrals.loadReferralData();
        }
    }
}); 