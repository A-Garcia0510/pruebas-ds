<?php
/**
 * Vista para mostrar los detalles de un pedido
 * 
 * Datos disponibles:
 * - $pedido: Array con los detalles del pedido
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('custom-coffee') ?>">

<div class="page-title">
    <h1>Detalles del Pedido</h1>
    <p>Información detallada de tu pedido de café personalizado</p>
</div>

<div class="recipes-container">
    <?php if (isset($pedido) && $pedido): ?>
        <div class="recipe-card">
            <div class="recipe-header">
                <h3>Pedido #<?= htmlspecialchars($pedido['orden_ID']) ?></h3>
                <span class="fecha">Fecha: <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></span>
            </div>

            <div class="recipe-content">
                <div class="componentes">
                    <h4>Componentes del Café</h4>
                    <ul>
                        <?php if (isset($pedido['detalles']) && is_array($pedido['detalles'])): ?>
                            <?php foreach ($pedido['detalles'] as $detalle): ?>
                                <li>
                                    <span class="component-name">
                                        <?= htmlspecialchars($detalle['nombre']) ?>
                                        <?php if (isset($detalle['tipo'])): ?>
                                            <small>(<?= htmlspecialchars($detalle['tipo']) ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                    <span class="component-quantity">
                                        <?= htmlspecialchars($detalle['cantidad']) ?> 
                                        <?= isset($detalle['unidad']) ? htmlspecialchars($detalle['unidad']) : 'unidad(es)' ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="estado-pedido">
                    <h4>Estado del Pedido</h4>
                    <div class="estado-badge <?= strtolower($pedido['estado']) ?>">
                        <?= ucfirst(htmlspecialchars($pedido['estado'])) ?>
                    </div>
                </div>

                <div class="precio">
                    <div class="price-breakdown">
                        <?php
                        $precioTotal = floatval($pedido['precio_total']);
                        $subtotal = round($precioTotal / 1.19, 2);
                        $iva = round($precioTotal - $subtotal, 2);
                        ?>
                        <div class="subtotal">
                            <span>Subtotal:</span>
                            <span class="amount">$<?= number_format($subtotal, 0, '.', ',') ?> CLP</span>
                        </div>
                        <div class="iva">
                            <span>IVA (19%):</span>
                            <span class="amount">$<?= number_format($iva, 0, '.', ',') ?> CLP</span>
                        </div>
                        <div class="total">
                            <span>Total:</span>
                            <span class="amount">$<?= number_format($precioTotal, 0, '.', ',') ?> CLP</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="recipe-actions">
                <?php if ($pedido['estado'] === 'pendiente'): ?>
                    <button class="cancel-btn" onclick="cancelarPedido(<?= $pedido['orden_ID'] ?>)">
                        Cancelar Pedido
                    </button>
                <?php endif; ?>
                <a href="<?= AssetHelper::url('custom-coffee/recipes') ?>" class="btn">Volver a Recetas</a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No se encontró el pedido solicitado</p>
            <a href="/custom-coffee/recipes" class="btn">Volver a Recetas</a>
        </div>
    <?php endif; ?>
</div>

<script>
function cancelarPedido(pedidoId) {
    if (confirm('¿Estás seguro de que deseas cancelar este pedido?')) {
        fetch(`<?= AssetHelper::url('api/custom-coffee/order') ?>/${pedidoId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pedido cancelado exitosamente');
                window.location.href = '<?= AssetHelper::url('custom-coffee/recipes') ?>';
            } else {
                alert(data.message || 'Error al cancelar el pedido');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}
</script>

<style>
.estado-pedido {
    background: var(--light-color);
    padding: 1.2rem;
    border-radius: var(--border-radius);
}

.estado-pedido h4 {
    color: var(--primary-color);
    font-size: 1.2rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--secondary-color);
}

.estado-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
}

.estado-badge.pendiente {
    background: var(--warning-color);
    color: white;
}

.estado-badge.preparando {
    background: var(--primary-color);
    color: white;
}

.estado-badge.completado {
    background: var(--success-color);
    color: white;
}

.estado-badge.cancelado {
    background: var(--danger-color);
    color: white;
}

.cancel-btn {
    background: var(--danger-color);
    color: white;
    border: none;
    border-radius: 5px;
    padding: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
    flex: 1;
}

.cancel-btn:hover {
    background: #B71C1C;
    transform: translateY(-2px);
}

.recipe-actions .btn {
    flex: 1;
    text-align: center;
    text-decoration: none;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    padding: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
}

.recipe-actions .btn:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
}

.price-breakdown {
    background: var(--light-color);
    padding: 1.2rem;
    border-radius: var(--border-radius);
    margin-top: 1rem;
}

.price-breakdown > div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.price-breakdown .total {
    border-top: 2px solid var(--secondary-color);
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    font-weight: bold;
    font-size: 1.1em;
}

.price-breakdown .amount {
    font-family: monospace;
    font-size: 1.1em;
}

.price-breakdown .iva {
    color: var(--text-muted);
    font-size: 0.9em;
}
</style> 