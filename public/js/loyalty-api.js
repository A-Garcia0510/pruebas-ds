/**
 * LoyaltyAPI - Clase para manejar la comunicación con la API de fidelización
 */
if (typeof window.LoyaltyAPI === 'undefined') {
    class LoyaltyAPI {
        constructor() {
            this.baseUrl = 'http://127.0.0.1:8000/api';
            this.userId = window.currentUserId || null;
        }
        
        /**
         * Realizar petición HTTP
         */
        async makeRequest(endpoint, options = {}) {
            const url = `${this.baseUrl}${endpoint}`;
            
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };
            
            const finalOptions = { ...defaultOptions, ...options };
            
            try {
                const response = await fetch(url, finalOptions);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            } catch (error) {
                console.error('Error en LoyaltyAPI:', error);
                throw error;
            }
        }
        
        /**
         * Obtener perfil de fidelización del usuario
         */
        async getUserProfile(userId = null) {
            const id = userId || this.userId;
            if (!id) {
                throw new Error('User ID no disponible');
            }
            
            return this.makeRequest(`/v1/loyalty/profile/${id}`);
        }
        
        /**
         * Obtener recompensas disponibles
         */
        async getRewards() {
            return this.makeRequest('/v1/loyalty/rewards');
        }
        
        /**
         * Canjear recompensa
         */
        async redeemReward(rewardId, userId = null) {
            const id = userId || this.userId;
            if (!id) {
                throw new Error('User ID no disponible');
            }
            
            return this.makeRequest('/v1/loyalty/redeem-reward', {
                method: 'POST',
                body: JSON.stringify({ 
                    user_id: id,
                    reward_id: rewardId 
                })
            });
        }
        
        /**
         * Obtener transacciones del usuario
         */
        async getUserTransactions(userId = null, page = 1) {
            const id = userId || this.userId;
            if (!id) {
                throw new Error('User ID no disponible');
            }
            
            return this.makeRequest(`/v1/loyalty/transactions/${id}?page=${page}`);
        }
        
        /**
         * Verificar estado de la API
         */
        async healthCheck() {
            return this.makeRequest('/health');
        }
        
        /**
         * Manejar errores de manera consistente
         */
        handleError(error) {
            console.error('Error en LoyaltyAPI:', error);
            
            let message = 'Error desconocido';
            let type = 'error';
            
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                message = 'Error de conexión con el servidor';
                type = 'warning';
            } else if (error.message.includes('404')) {
                message = 'Recurso no encontrado';
                type = 'warning';
            } else if (error.message.includes('500')) {
                message = 'Error interno del servidor';
                type = 'error';
            } else if (error.message.includes('401')) {
                message = 'No autorizado';
                type = 'warning';
            } else if (error.message.includes('403')) {
                message = 'Acceso denegado';
                type = 'warning';
            } else {
                message = error.message || 'Error desconocido';
                type = 'error';
            }
            
            this.showNotification(message, type);
            
            return {
                success: false,
                error: error.message,
                type: type
            };
        }
        
        /**
         * Mostrar notificación
         */
        showNotification(message, type = 'info') {
            // Crear elemento de notificación
            const notification = document.createElement('div');
            notification.className = `loyalty-notification loyalty-notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas ${this.getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                    <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            // Agregar estilos si no existen
            if (!document.getElementById('loyalty-notification-styles')) {
                const styles = document.createElement('style');
                styles.id = 'loyalty-notification-styles';
                styles.textContent = `
                    .loyalty-notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                        max-width: 400px;
                        padding: 15px;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        animation: slideIn 0.3s ease-out;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }
                    
                    .loyalty-notification-info {
                        background: #e3f2fd;
                        border-left: 4px solid #2196f3;
                        color: #1976d2;
                    }
                    
                    .loyalty-notification-success {
                        background: #e8f5e8;
                        border-left: 4px solid #4caf50;
                        color: #2e7d32;
                    }
                    
                    .loyalty-notification-warning {
                        background: #fff3e0;
                        border-left: 4px solid #ff9800;
                        color: #f57c00;
                    }
                    
                    .loyalty-notification-error {
                        background: #ffebee;
                        border-left: 4px solid #f44336;
                        color: #c62828;
                    }
                    
                    .notification-content {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    
                    .notification-close {
                        background: none;
                        border: none;
                        cursor: pointer;
                        padding: 0;
                        margin-left: auto;
                        opacity: 0.7;
                        transition: opacity 0.2s;
                    }
                    
                    .notification-close:hover {
                        opacity: 1;
                    }
                    
                    @keyframes slideIn {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                `;
                document.head.appendChild(styles);
            }
            
            // Agregar al DOM
            document.body.appendChild(notification);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        /**
         * Obtener icono para el tipo de notificación
         */
        getNotificationIcon(type) {
            const icons = {
                'info': 'fa-info-circle',
                'success': 'fa-check-circle',
                'warning': 'fa-exclamation-triangle',
                'error': 'fa-times-circle'
            };
            return icons[type] || icons.info;
        }
        
        /**
         * Verificar si la API está disponible
         */
        async isAvailable() {
            try {
                await this.healthCheck();
                return true;
            } catch (error) {
                return false;
            }
        }
        
        /**
         * Cambiar URL base de la API
         */
        setBaseURL(url) {
            this.baseUrl = url;
        }
    }
    
    // Crear instancia global
    window.LoyaltyAPI = LoyaltyAPI;
}

// Función global para mostrar notificaciones
function showLoyaltyNotification(message, type = 'info') {
    if (window.loyaltyAPI) {
        window.loyaltyAPI.showNotification(message, type);
    } else {
        console.log(`[Loyalty] ${type.toUpperCase()}: ${message}`);
    }
}

// Crear instancia global solo si no existe
if (typeof window.loyaltyAPI === 'undefined') {
    window.loyaltyAPI = new window.LoyaltyAPI();
}

// Función para mostrar notificaciones
function showLoyaltyNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `loyalty-notification loyalty-notification-${type}`;
    notification.textContent = message;
    
    // Agregar estilos básicos
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    `;
    
    // Colores según tipo
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#28a745';
            break;
        case 'error':
            notification.style.backgroundColor = '#dc3545';
            break;
        case 'warning':
            notification.style.backgroundColor = '#ffc107';
            notification.style.color = '#212529';
            break;
        default:
            notification.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(notification);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Animaciones CSS
if (!document.getElementById('loyalty-animation-styles')) {
    var style = document.createElement('style');
    style.id = 'loyalty-animation-styles';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { LoyaltyAPI, showLoyaltyNotification };
} 