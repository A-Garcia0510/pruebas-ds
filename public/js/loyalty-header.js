/**
 * LoyaltyHeader - Maneja la visualización de puntos y nivel en el header
 */
if (typeof window.LoyaltyHeader === 'undefined') {
    class LoyaltyHeader {
        constructor() {
            this.userId = window.currentUserId || null;
            this.pointsElement = null;
            this.tierElement = null;
            this.init();
        }

        /**
         * Inicializa el componente
         */
        init() {
            this.findElements();
            this.loadUserData();
            this.setupRefreshInterval();
        }

        /**
         * Busca los elementos del DOM donde mostrar los datos
         */
        findElements() {
            // Buscar elementos existentes o crear nuevos
            this.pointsElement = document.getElementById('loyalty-points') || this.createPointsElement();
            this.tierElement = document.getElementById('loyalty-tier') || this.createTierElement();
        }

        /**
         * Crea el elemento para mostrar puntos si no existe
         */
        createPointsElement() {
            const element = document.createElement('span');
            element.id = 'loyalty-points';
            element.className = 'loyalty-points';
            element.innerHTML = '<i class="fas fa-star"></i> <span class="points-value">0</span> pts';
            
            // Insertar en el header si existe
            const header = document.querySelector('header') || document.querySelector('.header') || document.querySelector('nav');
            if (header) {
                const container = header.querySelector('.user-info') || header.querySelector('.nav-right') || header;
                container.appendChild(element);
            }
            
            return element;
        }

        /**
         * Crea el elemento para mostrar el nivel si no existe
         */
        createTierElement() {
            const element = document.createElement('span');
            element.id = 'loyalty-tier';
            element.className = 'loyalty-tier';
            element.innerHTML = '<span class="tier-badge tier-bronze">Bronze</span>';
            
            // Insertar junto a los puntos
            if (this.pointsElement && this.pointsElement.parentNode) {
                this.pointsElement.parentNode.appendChild(element);
            }
            
            return element;
        }

        /**
         * Carga los datos del usuario desde la API
         */
        async loadUserData() {
            console.log('LoyaltyHeader: Iniciando carga de datos de usuario');
            console.log('LoyaltyHeader: window.currentUserId =', window.currentUserId);
            console.log('LoyaltyHeader: this.userId =', this.userId);
            
            if (!this.userId) {
                console.log('LoyaltyHeader: No hay usuario autenticado para mostrar datos de fidelización');
                console.log('LoyaltyHeader: window.currentUserId =', window.currentUserId);
                console.log('LoyaltyHeader: typeof window.currentUserId =', typeof window.currentUserId);
                return;
            }

            console.log('LoyaltyHeader: Usuario encontrado, cargando datos...');

            try {
                if (window.loyaltyAPI) {
                    console.log('LoyaltyHeader: Usando window.loyaltyAPI');
                    const profile = await window.loyaltyAPI.getUserProfile(this.userId);
                    console.log('LoyaltyHeader: Perfil obtenido:', profile);
                    this.updateDisplay(profile);
                } else {
                    console.log('LoyaltyHeader: Fallback - haciendo petición directa');
                    // Fallback: hacer petición directa
                    const response = await fetch(`http://127.0.0.1:8000/api/v1/loyalty/profile/${this.userId}`);
                    if (response.ok) {
                        const profile = await response.json();
                        console.log('LoyaltyHeader: Perfil obtenido (fallback):', profile);
                        this.updateDisplay(profile.data || profile);
                    }
                }
            } catch (error) {
                console.error('LoyaltyHeader: Error cargando datos de fidelización:', error);
                this.showOfflineMode();
            }
        }

        /**
         * Actualiza la visualización con los datos del usuario
         */
        updateDisplay(profile) {
            if (!profile) return;

            // Actualizar puntos
            if (this.pointsElement) {
                const pointsValue = this.pointsElement.querySelector('.points-value');
                if (pointsValue) {
                    pointsValue.textContent = profile.current_points || profile.points || 0;
                }
            }

            // Actualizar nivel
            if (this.tierElement) {
                const tier = profile.current_tier || profile.tier || 'cafe_bronze';
                const tierName = this.getTierDisplayName(tier);
                const tierClass = this.getTierClass(tier);
                
                this.tierElement.innerHTML = `<span class="tier-badge ${tierClass}">${tierName}</span>`;
            }

            // Mostrar progreso si está disponible
            if (profile.progress_percentage !== undefined) {
                this.showProgress(profile.progress_percentage);
            }
        }

        /**
         * Obtiene el nombre de visualización del nivel
         */
        getTierDisplayName(tier) {
            const tierNames = {
                'cafe_bronze': 'Bronze',
                'cafe_silver': 'Silver', 
                'cafe_gold': 'Gold',
                'cafe_diamond': 'Diamond'
            };
            return tierNames[tier] || 'Bronze';
        }

        /**
         * Obtiene la clase CSS del nivel
         */
        getTierClass(tier) {
            const tierClasses = {
                'cafe_bronze': 'tier-bronze',
                'cafe_silver': 'tier-silver',
                'cafe_gold': 'tier-gold', 
                'cafe_diamond': 'tier-diamond'
            };
            return tierClasses[tier] || 'tier-bronze';
        }

        /**
         * Muestra el progreso hacia el siguiente nivel
         */
        showProgress(percentage) {
            // Crear o actualizar barra de progreso
            let progressBar = document.getElementById('loyalty-progress');
            if (!progressBar) {
                progressBar = document.createElement('div');
                progressBar.id = 'loyalty-progress';
                progressBar.className = 'loyalty-progress';
                progressBar.innerHTML = `
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${percentage}%"></div>
                    </div>
                    <span class="progress-text">${percentage}%</span>
                `;
                
                if (this.tierElement && this.tierElement.parentNode) {
                    this.tierElement.parentNode.appendChild(progressBar);
                }
            } else {
                const fill = progressBar.querySelector('.progress-fill');
                const text = progressBar.querySelector('.progress-text');
                if (fill) fill.style.width = `${percentage}%`;
                if (text) text.textContent = `${percentage}%`;
            }
        }

        /**
         * Modo offline cuando la API no está disponible
         */
        showOfflineMode() {
            if (this.pointsElement) {
                this.pointsElement.innerHTML = '<i class="fas fa-star"></i> <span class="points-value">--</span> pts';
            }
            if (this.tierElement) {
                this.tierElement.innerHTML = '<span class="tier-badge tier-offline">Offline</span>';
            }
        }

        /**
         * Configura el intervalo de actualización automática
         */
        setupRefreshInterval() {
            // Actualizar cada 30 segundos si el usuario está activo
            setInterval(() => {
                if (!document.hidden && this.userId) {
                    this.loadUserData();
                }
            }, 30000);
        }

        /**
         * Actualiza los puntos después de una compra
         */
        updatePointsAfterPurchase(newPoints) {
            if (this.pointsElement) {
                const pointsValue = this.pointsElement.querySelector('.points-value');
                if (pointsValue) {
                    // Animación de actualización
                    const oldValue = parseInt(pointsValue.textContent) || 0;
                    this.animatePointsChange(oldValue, newPoints);
                }
            }
        }

        /**
         * Anima el cambio de puntos
         */
        animatePointsChange(oldValue, newValue) {
            const pointsValue = this.pointsElement.querySelector('.points-value');
            if (!pointsValue) return;

            const difference = newValue - oldValue;
            const duration = 1000; // 1 segundo
            const steps = 20;
            const stepValue = difference / steps;
            const stepTime = duration / steps;

            let currentStep = 0;
            const interval = setInterval(() => {
                currentStep++;
                const currentValue = Math.round(oldValue + (stepValue * currentStep));
                pointsValue.textContent = currentValue;

                if (currentStep >= steps) {
                    clearInterval(interval);
                    pointsValue.textContent = newValue;
                    
                    // Mostrar notificación de puntos ganados
                    if (difference > 0) {
                        this.showPointsEarnedNotification(difference);
                    }
                }
            }, stepTime);
        }

        /**
         * Muestra notificación de puntos ganados
         */
        showPointsEarnedNotification(points) {
            if (window.showLoyaltyNotification) {
                window.showLoyaltyNotification(`¡Ganaste ${points} puntos!`, 'success');
            } else {
                // Fallback: alert simple
                alert(`¡Ganaste ${points} puntos!`);
            }
        }

        /**
         * Refresca los datos manualmente
         */
        refresh() {
            this.loadUserData();
        }
    }
    window.LoyaltyHeader = LoyaltyHeader;
}

// Inicializar cuando el DOM esté listo, solo si no existe
if (typeof window.loyaltyHeader === 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        window.loyaltyHeader = new window.LoyaltyHeader();
    });
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LoyaltyHeader;
} 