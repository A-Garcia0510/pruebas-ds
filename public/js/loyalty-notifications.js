// loyalty-notifications.js
// Módulo para mostrar notificaciones visuales de fidelización

function showLoyaltyNotification(message, type = 'success', duration = 3500) {
    // Elimina notificaciones previas
    document.querySelectorAll('.notification.loyalty').forEach(n => n.remove());
    
    const notif = document.createElement('div');
    notif.className = `notification loyalty ${type}`;
    notif.textContent = message;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), duration);
}

// Ejemplo de uso:
// showLoyaltyNotification('¡Ganaste 100 puntos!', 'success');
// showLoyaltyNotification('¡Subiste a Café Oro!', 'info');
// showLoyaltyNotification('¡Canje exitoso!', 'success');
// showLoyaltyNotification('Tus puntos están por expirar', 'warning');

// Para usar: incluir <script src="/public/js/loyalty-notifications.js"></script> en tus páginas. 