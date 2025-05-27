<?php
/**
 * Vista para mostrar los detalles de un pedido
 * 
 * Datos disponibles:
 * - $pedido: Array con los detalles del pedido
 * - $isLoggedIn: Indica si el usuario está logueado
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';

// Función helper para formatear el estado
function formatEstado($estado) {
    $estados = [
        'pendiente' => ['texto' => 'Pendiente', 'clase' => 'estado-pendiente'],
        'preparando' => ['texto' => 'En Preparación', 'clase' => 'estado-preparando'],
        'completado' => ['texto' => 'Completado', 'clase' => 'estado-completado'],
        'cancelado' => ['texto' => 'Cancelado', 'clase' => 'estado-cancelado']
    ];
    
    return $estados[$estado] ?? ['texto' => ucfirst($estado), 'clase' => 'estado-desconocido'];
}

$estado = formatEstado($pedido['estado']);
?>

<link rel="stylesheet" href="<?= AssetHelper::css('custom-coffee') ?>">

<div class="page-title">
    <h1>Detalles del Pedido</h1>
    <a href="<?= AssetHelper::url('custom-coffee/orders') ?>" class="volver-link">
        <i>←</i> Volver a Mis Pedidos
    </a>
</div>

<div class="order-details-container">
    <?php if ($pedido): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <h2>Pedido #<?= $pedido['orden_ID'] ?></h2>
                    <span class="fecha"><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></span>
                </div>
                <div class="order-status">
                    <span class="estado <?= $estado['clase'] ?>"><?= $estado['texto'] ?></span>
                </div>
            </div>
            
            <div class="order-content">
                <div class="recipe-info">
                    <h3>Receta: <?= htmlspecialchars($pedido['nombre_receta']) ?></h3>
                    
                    <div class="components-list">
                        <h4>Componentes:</h4>
                        <ul>
                            <?php foreach ($pedido['detalles'] as $detalle): ?>
                                <li>
                                    <span class="component-name">
                                        <?= htmlspecialchars($detalle['nombre']) ?>
                                        <?php if (!empty($detalle['cantidad_unidad'])): ?>
                                            (<?= htmlspecialchars($detalle['cantidad_unidad']) ?>)
                                        <?php elseif (!empty($detalle['unidad'])): ?>
                                            (<?= htmlspecialchars($detalle['unidad']) ?>)
                                        <?php endif; ?>
                                    </span>
                                    <span class="component-quantity">x<?= $detalle['cantidad'] ?></span>
                                    <span class="component-price">
                                        $<?= number_format($detalle['precio_unitario'] * $detalle['cantidad'], 0, ',', '.') ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="price-summary">
                    <div class="subtotal">
                        <span>Subtotal:</span>
                        <span class="amount">$<?= number_format($pedido['precio_total'] / 1.19, 0, ',', '.') ?></span>
                    </div>
                    <div class="iva">
                        <span>IVA (19%):</span>
                        <span class="amount">$<?= number_format($pedido['precio_total'] - ($pedido['precio_total'] / 1.19), 0, ',', '.') ?></span>
                    </div>
                    <div class="total">
                        <span>Total:</span>
                        <span class="amount">$<?= number_format($pedido['precio_total'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($pedido['estado'] === 'pendiente'): ?>
                <div class="order-actions">
                    <button class="btn cancel-btn" onclick="cancelarPedido(<?= $pedido['orden_ID'] ?>)">
                        Cancelar Pedido
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="error-state">
            <p>No se encontró el pedido solicitado.</p>
            <a href="<?= AssetHelper::url('custom-coffee/orders') ?>" class="btn primary-btn">
                Ver Mis Pedidos
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($pedido && $pedido['estado'] === 'pendiente'): ?>
<script>
function cancelarPedido(pedidoId) {
    if (!confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
        return;
    }
    
    fetch('<?= AssetHelper::url('api/custom-coffee/cancel/') ?>' + pedidoId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pedido cancelado con éxito');
            window.location.reload();
        } else {
            alert('Error al cancelar el pedido: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar el pedido');
    });
}
</script>
<?php endif; ?> 