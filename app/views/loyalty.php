<?php
// Página principal de fidelización - loyalty.php
// Muestra el resumen de puntos, nivel y barra de progreso del usuario

session_start();
// Suponiendo que el usuario está autenticado y su ID está en $_SESSION['usuario_ID']
$usuario_ID = isset($_SESSION['usuario_ID']) ? $_SESSION['usuario_ID'] : null;

if (!$usuario_ID) {
    header('Location: /login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Fidelización Café-VT</title>
    <link rel="stylesheet" href="/public/css/common.css">
    <link rel="stylesheet" href="/public/css/loyalty.css">
    <style>
        .loyalty-summary { max-width: 500px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; padding: 2rem; text-align: center; }
        .loyalty-tier { font-size: 1.3rem; font-weight: bold; margin-bottom: 0.5rem; }
        .loyalty-points { font-size: 2.5rem; color: #b8860b; font-weight: bold; }
        .loyalty-progress { width: 100%; background: #eee; border-radius: 8px; height: 18px; margin: 1rem 0; }
        .loyalty-progress-bar { height: 18px; border-radius: 8px; background: linear-gradient(90deg, #b8860b, #ffd700); transition: width 0.5s; }
        .loyalty-benefits { margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="loyalty-summary">
        <div class="loyalty-tier" id="loyalty-tier">Nivel: ...</div>
        <div class="loyalty-points" id="loyalty-points">...</div>
        <div>puntos</div>
        <div class="loyalty-progress">
            <div class="loyalty-progress-bar" id="loyalty-progress-bar" style="width:0%"></div>
        </div>
        <div id="loyalty-next-tier">Progreso al siguiente nivel: ...</div>
        <div class="loyalty-benefits" id="loyalty-benefits"></div>
    </div>
    <script src="/public/js/loyalty-notifications.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Notificación de bienvenida
        showLoyaltyNotification('¡Bienvenido a tu panel de fidelización Café-VT!', 'info', 3000);
        // Cargar datos de fidelización del usuario desde la API
        fetch(`/api/loyalty/users/<?php echo $usuario_ID; ?>`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('loyalty-tier').textContent = 'Nivel: ' + (data.current_tier || '...');
                document.getElementById('loyalty-points').textContent = data.current_points || 0;
                // Calcular progreso al siguiente nivel
                const tierThresholds = { 'cafe_bronze': 0, 'cafe_plata': 1000, 'cafe_oro': 5000, 'cafe_diamante': 15000 };
                const nextTiers = { 'cafe_bronze': 'cafe_plata', 'cafe_plata': 'cafe_oro', 'cafe_oro': 'cafe_diamante', 'cafe_diamante': null };
                const current = data.current_tier;
                const points = data.current_points || 0;
                let next = nextTiers[current];
                let min = tierThresholds[current];
                let max = next ? tierThresholds[next] : points;
                let percent = next ? Math.min(100, Math.round(((points - min) / (max - min)) * 100)) : 100;
                document.getElementById('loyalty-progress-bar').style.width = percent + '%';
                document.getElementById('loyalty-next-tier').textContent = next ? `Te faltan ${max - points} puntos para ${next.replace('cafe_', 'Café ').replace('_', ' ')}` : '¡Máximo nivel alcanzado!';
                // Beneficios por nivel
                const benefits = {
                    'cafe_bronze': '5% de descuento en tu primera compra, acceso a promociones básicas.',
                    'cafe_plata': '10% de descuento, acceso a eventos exclusivos, 1 café gratis al mes.',
                    'cafe_oro': '15% de descuento, prioridad en lanzamientos, 2 cafés gratis al mes.',
                    'cafe_diamante': '20% de descuento, regalos exclusivos, cafés ilimitados en eventos.'
                };
                document.getElementById('loyalty-benefits').textContent = 'Beneficios: ' + (benefits[current] || '');
            })
            .catch(() => {
                document.getElementById('loyalty-tier').textContent = 'No se pudo cargar tu perfil de fidelización.';
            });
    });
    </script>
</body>
</html> 