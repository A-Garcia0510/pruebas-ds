<?php
/**
 * Vista que muestra todos los pedidos de café personalizado del usuario
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('custom-coffee') ?>">

<div class="page-title">
    <h1>Mis Pedidos de Café Personalizado</h1>
    <p>Gestiona y revisa tus pedidos de café personalizado</p>
            </div>

<div class="recipes-container">
    <?php if (isset($pedidos) && !empty($pedidos)): ?>
        <div class="orders-grid">
                <?php foreach ($pedidos as $pedido): ?>
                <div class="recipe-card">
                    <div class="recipe-header">
                        <h3>Pedido #<?= htmlspecialchars($pedido['orden_ID']) ?></h3>
                        <span class="fecha">Fecha: <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></span>
                    </div>

                    <div class="recipe-content">
                        <?php if (!empty($pedido['nombre_receta'])): ?>
                            <div class="recipe-info">
                                <h4>Receta: <?= htmlspecialchars($pedido['nombre_receta']) ?></h4>
                            </div>
                        <?php endif; ?>

                        <div class="componentes">
                            <h4>Componentes</h4>
                                <?php
                            $tipos = ['base', 'leche', 'endulzante', 'topping'];
                            foreach ($tipos as $tipo): 
                                $componentesTipo = array_filter($pedido['detalles'], function($detalle) use ($tipo) {
                                    return isset($detalle['tipo']) && $detalle['tipo'] === $tipo;
                                });
                                if (!empty($componentesTipo)):
                            ?>
                                <div class="component-type">
                                    <h5><?= ucfirst($tipo) ?>:</h5>
                                    <ul>
                                        <?php foreach ($componentesTipo as $detalle): ?>
                                            <li>
                                                <span class="component-name">
                                                    <?= htmlspecialchars($detalle['nombre']) ?>
                                                </span>
                                                <span class="component-details">
                                                    x<?= htmlspecialchars($detalle['cantidad']) ?>
                                                    <?php if (isset($detalle['unidad'])): ?>
                                                        <?= htmlspecialchars($detalle['unidad']) ?>
                                                    <?php endif; ?>
                                                    <span class="component-price">
                                                        $<?= number_format($detalle['precio'] * $detalle['cantidad'], 0, ',', '.') ?> CLP
                                                    </span>
                                                </span>
                                            </li>
                                    <?php endforeach; ?>
                                </ul>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
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

                        <div class="estado-pedido">
                            <h4>Estado del Pedido</h4>
                            <div class="estado-badge <?= strtolower($pedido['estado']) ?>">
                                <?= ucfirst(htmlspecialchars($pedido['estado'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="recipe-actions">
                        <a href="<?= AssetHelper::url('custom-coffee/order-details/' . $pedido['orden_ID']) ?>" class="btn">
                            Ver Detalles
                        </a>
                        <?php if ($pedido['estado'] === 'pendiente'): ?>
                            <button class="cancel-btn" onclick="cancelarPedido(<?= $pedido['orden_ID'] ?>)">
                                Cancelar Pedido
                            </button>
                        <?php endif; ?>
                    </div>
                    </div>
                <?php endforeach; ?>
            </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No tienes pedidos realizados</p>
            <a href="<?= AssetHelper::url('custom-coffee/recipes') ?>" class="btn">Ver Mis Recetas</a>
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
                    window.location.reload();
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