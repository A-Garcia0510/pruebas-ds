<?php
/**
 * Vista para listar las recetas guardadas
 * 
 * Datos disponibles:
 * - $recetas: Array de recetas del usuario
 * - $isLoggedIn: Indica si el usuario está logueado
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('custom-coffee') ?>">

<div class="page-title">
    <h1>Mis Recetas</h1>
    <p>Gestiona tus recetas de café personalizadas</p>
</div>

<div class="recipes-container">
    <?php if (isset($recetas) && !empty($recetas)): ?>
        <div class="recipes-grid">
            <?php foreach ($recetas as $receta): ?>
                <div class="recipe-card">
                    <div class="recipe-header">
                        <h3><?= htmlspecialchars($receta['nombre']) ?></h3>
                        <span class="fecha">Creada: <?= date('d/m/Y', strtotime($receta['fecha_creacion'])) ?></span>
                    </div>

                    <div class="recipe-content">
                        <div class="componentes">
                            <h4>Componentes</h4>
                            <ul>
                                <?php foreach ($receta['detalles'] as $detalle): ?>
                                    <li>
                                        <span class="component-name">
                                            <?= htmlspecialchars($detalle['nombre']) ?>
                                            <small>(<?= htmlspecialchars($detalle['tipo']) ?>)</small>
                                        </span>
                                        <span class="component-quantity">
                                            <?= htmlspecialchars($detalle['cantidad']) ?> <?= htmlspecialchars($detalle['unidad']) ?>
                                            <small class="component-price">
                                                $<?= number_format($detalle['precio_unitario'], 0, ',', '.') ?> c/u
                                            </small>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="precio">
                            <span>Precio Total (IVA Incluido)</span>
                            <span class="amount">$<?= number_format($receta['precio_total'], 0, ',', '.') ?> CLP</span>
                        </div>
                    </div>

                    <div class="recipe-actions">
                        <button class="order-btn" onclick="realizarPedido(<?= json_encode($receta['receta_ID']) ?>, <?= json_encode($receta['precio_total']) ?>)">
                            Realizar Pedido
                        </button>
                        <button class="delete-btn" onclick="eliminarReceta(<?= json_encode($receta['receta_ID']) ?>)">
                            Eliminar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="action-buttons">
            <a href="<?= AssetHelper::url('custom-coffee') ?>" class="btn">Crear Nueva Receta</a>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No tienes recetas guardadas</p>
            <a href="<?= AssetHelper::url('custom-coffee') ?>" class="btn">Crear Mi Primera Receta</a>
        </div>
    <?php endif; ?>
</div>

<script>
const baseUrl = window.location.origin + '/pruebas-ds/public';

async function realizarPedido(recetaId, precioTotal) {
    try {
        console.log('===== INICIO DE REALIZAR PEDIDO =====');
        console.log('Parámetros originales:', { recetaId, precioTotal });

        // Validar que los parámetros sean números válidos
        recetaId = parseInt(recetaId);
        precioTotal = parseFloat(precioTotal);
        
        console.log('Parámetros procesados:', { 
            recetaId, 
            precioTotal,
            recetaIdType: typeof recetaId,
            precioTotalType: typeof precioTotal
        });

        if (isNaN(recetaId) || recetaId <= 0) {
            throw new Error('ID de receta inválido');
        }

        if (isNaN(precioTotal) || precioTotal <= 0) {
            throw new Error('Precio total inválido');
        }

        if (!confirm('¿Deseas realizar un pedido con esta receta?')) {
            return;
        }

        const requestData = {
            receta_id: recetaId,
            precio_total: precioTotal
        };
        
        console.log('Datos a enviar:', requestData);

        const response = await fetch(`${baseUrl}/api/custom-coffee/place-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(requestData)
        });

        console.log('Status de la respuesta:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error HTTP:', response.status, errorText);
            throw new Error(`Error del servidor: ${response.status}`);
        }

        const responseText = await response.text();
        console.log('Respuesta raw:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            throw new Error('Error al procesar la respuesta del servidor');
        }

        console.log('Datos de la respuesta:', data);

        if (data.success) {
            alert('Pedido realizado con éxito');
            window.location.href = `${baseUrl}/custom-coffee/orders`;
        } else {
            throw new Error(data.message || 'Error al realizar el pedido');
        }
    } catch (error) {
        console.error('Error completo:', error);
        alert(error.message || 'Error al realizar el pedido');
    }
}

async function eliminarReceta(recetaId) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta receta?')) {
        return;
    }

    try {
        const url = `${baseUrl}/api/custom-coffee/delete-recipe/${recetaId}`;
        console.log('Enviando petición a:', url);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        const contentType = response.headers.get('content-type');
        console.log('Content-Type de la respuesta:', contentType);

        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Respuesta no-JSON recibida:', text);
            throw new Error('La respuesta del servidor no es JSON válido');
        }

        const data = await response.json();
        console.log('Respuesta del servidor:', data);

        if (!response.ok) {
            throw new Error(data.message || `Error HTTP: ${response.status}`);
        }

        if (data.success) {
            alert(data.message || 'Receta eliminada exitosamente');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Error al eliminar la receta');
        }
    } catch (error) {
        console.error('Error al eliminar la receta:', error);
        alert(error.message || 'Error al procesar la solicitud');
    }
}
</script> 