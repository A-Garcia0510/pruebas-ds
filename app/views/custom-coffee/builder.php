<?php
/**
 * Vista del constructor de café personalizado
 * 
 * Datos disponibles:
 * - $componentes: Array de componentes agrupados por tipo
 * - $isLoggedIn: Indica si el usuario está logueado
 */

require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('custom-coffee') ?>">

<div class="page-title">
    <h1>Constructor de Café Personalizado</h1>
    <p>Crea tu café perfecto seleccionando cada componente</p>
</div>

<div class="coffee-builder-container">
    <!-- Panel de Componentes -->
    <div class="components-panel">
        <div class="component-section">
            <h3>Base</h3>
            <div class="component-options horizontal-scroll">
                <?php foreach ($componentes['base'] as $base): ?>
                    <div class="component-card" data-id="<?= $base['componente_ID'] ?>" data-tipo="base">
                        <h4><?= htmlspecialchars($base['nombre']) ?><?php if (!empty($base['cantidad_unidad'])): ?> (<?= htmlspecialchars($base['cantidad_unidad']) ?>)<?php elseif (!empty($base['unidad'])): ?> (<?= htmlspecialchars($base['unidad']) ?>)<?php endif; ?></h4>
                        <p class="precio">$<?= number_format($base['precio'], 0, ',', '.') ?></p>
                        <p class="stock">Stock: <?= $base['stock'] ?></p>
                        <button class="select-btn" <?= $base['stock'] <= 0 ? 'disabled' : '' ?>>
                            Seleccionar
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="component-section">
            <h3>Leche</h3>
            <div class="component-options horizontal-scroll">
                <?php foreach ($componentes['leche'] as $leche): ?>
                    <div class="component-card" data-id="<?= $leche['componente_ID'] ?>" data-tipo="leche">
                        <h4><?= htmlspecialchars($leche['nombre']) ?><?php if (!empty($leche['cantidad_unidad'])): ?> (<?= htmlspecialchars($leche['cantidad_unidad']) ?>)<?php elseif (!empty($leche['unidad'])): ?> (<?= htmlspecialchars($leche['unidad']) ?>)<?php endif; ?></h4>
                        <p class="precio">$<?= number_format($leche['precio'], 0, ',', '.') ?></p>
                        <p class="stock">Stock: <?= $leche['stock'] ?></p>
                        <div class="quantity-controls">
                            <button class="decrease-btn" disabled>-</button>
                            <input type="number" value="0" min="0" max="<?= $leche['stock'] ?>" readonly>
                            <button class="increase-btn" <?= $leche['stock'] <= 0 ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="component-section">
            <h3>Endulzantes</h3>
            <div class="component-options horizontal-scroll">
                <?php foreach ($componentes['endulzante'] as $endulzante): ?>
                    <div class="component-card" data-id="<?= $endulzante['componente_ID'] ?>" data-tipo="endulzante">
                        <h4><?= htmlspecialchars($endulzante['nombre']) ?><?php if (!empty($endulzante['cantidad_unidad'])): ?> (<?= htmlspecialchars($endulzante['cantidad_unidad']) ?>)<?php elseif (!empty($endulzante['unidad'])): ?> (<?= htmlspecialchars($endulzante['unidad']) ?>)<?php endif; ?></h4>
                        <p class="precio">$<?= number_format($endulzante['precio'], 0, ',', '.') ?></p>
                        <p class="stock">Stock: <?= $endulzante['stock'] ?></p>
                        <div class="quantity-controls">
                            <button class="decrease-btn" disabled>-</button>
                            <input type="number" value="0" min="0" max="<?= $endulzante['stock'] ?>" readonly>
                            <button class="increase-btn" <?= $endulzante['stock'] <= 0 ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="component-section">
            <h3>Toppings</h3>
            <div class="component-options horizontal-scroll">
                <?php foreach ($componentes['topping'] as $topping): ?>
                    <div class="component-card" data-id="<?= $topping['componente_ID'] ?>" data-tipo="topping">
                        <h4><?= htmlspecialchars($topping['nombre']) ?><?php if (!empty($topping['cantidad_unidad'])): ?> (<?= htmlspecialchars($topping['cantidad_unidad']) ?>)<?php elseif (!empty($topping['unidad'])): ?> (<?= htmlspecialchars($topping['unidad']) ?>)<?php endif; ?></h4>
                        <p class="precio">$<?= number_format($topping['precio'], 0, ',', '.') ?></p>
                        <p class="stock">Stock: <?= $topping['stock'] ?></p>
                        <div class="quantity-controls">
                            <button class="decrease-btn" disabled>-</button>
                            <input type="number" value="0" min="0" max="<?= $topping['stock'] ?>" readonly>
                            <button class="increase-btn" <?= $topping['stock'] <= 0 ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Panel de Resumen -->
    <div class="summary-panel">
        <div class="coffee-preview">
            <h3>Tu Café Personalizado</h3>
            <div class="preview-content">
                <div class="selected-components">
                    <div class="base-section">
                        <h4>Base</h4>
                        <div id="selected-base">No seleccionada</div>
                    </div>
                    <div class="milk-section">
                        <h4>Leche</h4>
                        <div id="selected-milk">No seleccionada</div>
                    </div>
                    <div class="sweetener-section">
                        <h4>Endulzantes</h4>
                        <div id="selected-sweeteners">No seleccionados</div>
                    </div>
                    <div class="topping-section">
                        <h4>Toppings</h4>
                        <div id="selected-toppings">No seleccionados</div>
                    </div>
                </div>
                <div class="price-summary">
                    <div class="subtotal">
                        <span>Subtotal:</span>
                        <span id="subtotal-price">$0</span>
                    </div>
                    <div class="iva">
                        <span>IVA (19%):</span>
                        <span id="iva-price">$0</span>
                    </div>
                    <div class="total">
                        <span>Total:</span>
                        <span id="total-price">$0</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
            <div class="action-buttons">
                <button id="save-recipe-btn" class="btn" disabled>
                    Guardar Receta
                </button>
                <button id="order-btn" class="btn primary-btn" disabled>
                    Realizar Pedido
                </button>
            </div>
        <?php else: ?>
            <div class="login-required">
                <p>Para guardar recetas y realizar pedidos, debes <a href="<?= AssetHelper::url('login') ?>">iniciar sesión</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para guardar receta -->
<div id="save-recipe-modal" class="modal">
    <div class="modal-content">
        <h3>Guardar Receta</h3>
        <form id="save-recipe-form">
            <div class="form-group">
                <label for="recipe-name">Nombre de la receta:</label>
                <input type="text" id="recipe-name" name="nombre" required>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn primary-btn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedComponents = {
        base: null,
        milk: [],
        sweeteners: [],
        toppings: []
    };

    // Funciones de utilidad
    function formatPrice(price) {
        return Math.round(price).toLocaleString('es-CL');
    }

    function updatePrice() {
        let subtotal = 0;
        
        // Sumar base
        if (selectedComponents.base) {
            subtotal += selectedComponents.base.precio;
        }
        
        // Sumar leche
        selectedComponents.milk.forEach(milk => {
            subtotal += milk.precio * milk.cantidad;
        });
        
        // Sumar endulzantes
        selectedComponents.sweeteners.forEach(sweetener => {
            subtotal += sweetener.precio * sweetener.cantidad;
        });
        
        // Sumar toppings
        selectedComponents.toppings.forEach(topping => {
            subtotal += topping.precio * topping.cantidad;
        });
        
        const iva = subtotal * 0.19;
        const total = subtotal + iva;
        
        document.getElementById('subtotal-price').textContent = `$${formatPrice(subtotal)}`;
        document.getElementById('iva-price').textContent = `$${formatPrice(iva)}`;
        document.getElementById('total-price').textContent = `$${formatPrice(total)}`;
        
        // Actualizar estado de botones
        const hasBase = selectedComponents.base !== null;
        document.getElementById('save-recipe-btn').disabled = !hasBase;
        document.getElementById('order-btn').disabled = !hasBase;
    }

    function updateComponentDisplay() {
        // Actualizar base
        const baseDisplay = document.getElementById('selected-base');
        baseDisplay.textContent = selectedComponents.base ? 
            `${selectedComponents.base.nombre} (${selectedComponents.base.cantidad_unidad || selectedComponents.base.unidad}) - $${formatPrice(selectedComponents.base.precio)}` : 
            'No seleccionada';
        
        // Actualizar leche
        const milkDisplay = document.getElementById('selected-milk');
        milkDisplay.innerHTML = selectedComponents.milk.length > 0 ?
            selectedComponents.milk.map(m => 
                `${m.nombre} x${m.cantidad} (${m.cantidad_unidad || m.unidad}) - $${formatPrice(m.precio * m.cantidad)}`
            ).join('<br>') :
            'No seleccionada';
        
        // Actualizar endulzantes
        const sweetenersDisplay = document.getElementById('selected-sweeteners');
        sweetenersDisplay.innerHTML = selectedComponents.sweeteners.length > 0 ?
            selectedComponents.sweeteners.map(s => 
                `${s.nombre} x${s.cantidad} (${s.cantidad_unidad || s.unidad}) - $${formatPrice(s.precio * s.cantidad)}`
            ).join('<br>') :
            'No seleccionados';
        
        // Actualizar toppings
        const toppingsDisplay = document.getElementById('selected-toppings');
        toppingsDisplay.innerHTML = selectedComponents.toppings.length > 0 ?
            selectedComponents.toppings.map(t => 
                `${t.nombre} x${t.cantidad} (${t.cantidad_unidad || t.unidad}) - $${formatPrice(t.precio * t.cantidad)}`
            ).join('<br>') :
            'No seleccionados';
        
        updatePrice();
    }

    // Event Listeners para componentes
    document.querySelectorAll('.component-card').forEach(card => {
        const tipo = card.dataset.tipo;
        const id = parseInt(card.dataset.id);
        const nombre = card.querySelector('h4').textContent.split(' (')[0]; // Extraer solo el nombre sin la unidad
        const precio = parseInt(card.querySelector('.precio').textContent.replace(/[^0-9]/g, ''));
        const stock = parseInt(card.querySelector('.stock').textContent.replace(/[^0-9.-]+/g, ''));
        const cantidadUnidad = card.querySelector('h4').textContent.match(/\((.*?)\)/)?.[1] || '';
        const unidad = cantidadUnidad.split(' ')[1] || '';
        
        if (tipo === 'base') {
            card.querySelector('.select-btn').addEventListener('click', function() {
                if (this.disabled) return;
                
                document.querySelectorAll('.component-card[data-tipo="base"] .select-btn')
                    .forEach(btn => btn.classList.remove('selected'));
                
                this.classList.add('selected');
                selectedComponents.base = { 
                    id, 
                    nombre, 
                    precio, 
                    cantidad_unidad: cantidadUnidad,
                    unidad: unidad 
                };
                updateComponentDisplay();
            });
        } else {
            const decreaseBtn = card.querySelector('.decrease-btn');
            const increaseBtn = card.querySelector('.increase-btn');
            const quantityInput = card.querySelector('input');
            
            decreaseBtn.addEventListener('click', function() {
                let cantidad = parseInt(quantityInput.value);
                if (cantidad > 0) {
                    cantidad--;
                    quantityInput.value = cantidad;
                    let array;
                    if (tipo === 'leche') array = selectedComponents.milk;
                    else if (tipo === 'endulzante') array = selectedComponents.sweeteners;
                    else array = selectedComponents[`${tipo}s`];
                    const index = array.findIndex(c => c.id === id);
                    if (index !== -1) {
                        if (cantidad === 0) {
                            array.splice(index, 1);
                        } else {
                            array[index].cantidad = cantidad;
                        }
                    }
                    this.disabled = cantidad === 0;
                    updateComponentDisplay();
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                let cantidad = parseInt(quantityInput.value);
                if (cantidad < stock) {
                    cantidad++;
                    quantityInput.value = cantidad;
                    let array;
                    if (tipo === 'leche') array = selectedComponents.milk;
                    else if (tipo === 'endulzante') array = selectedComponents.sweeteners;
                    else array = selectedComponents[`${tipo}s`];
                    const index = array.findIndex(c => c.id === id);
                    if (index === -1) {
                        array.push({ 
                            id, 
                            nombre, 
                            precio, 
                            cantidad,
                            cantidad_unidad: cantidadUnidad,
                            unidad: unidad 
                        });
                    } else {
                        array[index].cantidad = cantidad;
                    }
                    decreaseBtn.disabled = false;
                    updateComponentDisplay();
                }
            });
        }
    });

    // Event Listeners para botones de acción
    document.getElementById('save-recipe-btn').addEventListener('click', function() {
        document.getElementById('save-recipe-modal').style.display = 'block';
    });

    document.getElementById('save-recipe-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const nombre = document.getElementById('recipe-name').value;
        const componentes = [
            { 
                componente_ID: selectedComponents.base.id, 
                tipo: 'base', 
                cantidad: 1,
                precio: selectedComponents.base.precio
            },
            ...selectedComponents.milk.map(m => ({ 
                componente_ID: m.id, 
                tipo: 'leche', 
                cantidad: m.cantidad,
                precio: m.precio
            })),
            ...selectedComponents.sweeteners.map(s => ({ 
                componente_ID: s.id, 
                tipo: 'endulzante', 
                cantidad: s.cantidad,
                precio: s.precio
            })),
            ...selectedComponents.toppings.map(t => ({ 
                componente_ID: t.id, 
                tipo: 'topping', 
                cantidad: t.cantidad,
                precio: t.precio
            }))
        ];
        
        const precioTotal = parseFloat(document.getElementById('total-price').textContent.replace(/[^0-9.-]+/g, ''));
        
        fetch('<?= AssetHelper::url('api/custom-coffee/save-recipe') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nombre,
                componentes,
                precio_total: precioTotal
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Receta guardada con éxito');
                closeModal();
                document.getElementById('save-recipe-form').reset();
            } else {
                alert('Error al guardar la receta: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la receta');
        });
    });

    document.getElementById('order-btn').addEventListener('click', function() {
        // Validar que haya una base seleccionada
        if (!selectedComponents.base) {
            alert('Debes seleccionar una base para tu café');
            return;
        }

        if (!confirm('¿Deseas realizar el pedido con los componentes seleccionados?')) {
            return;
        }
        
        const componentes = [
            { 
                componente_ID: selectedComponents.base.id, 
                tipo: 'base', 
                cantidad: selectedComponents.base.cantidad || 1,
                precio: selectedComponents.base.precio
            },
            ...selectedComponents.milk.map(m => ({ 
                componente_ID: m.id, 
                tipo: 'leche', 
                cantidad: m.cantidad || 1,
                precio: m.precio
            })),
            ...selectedComponents.sweeteners.map(s => ({ 
                componente_ID: s.id, 
                tipo: 'endulzante', 
                cantidad: s.cantidad || 1,
                precio: s.precio
            })),
            ...selectedComponents.toppings.map(t => ({ 
                componente_ID: t.id, 
                tipo: 'topping', 
                cantidad: t.cantidad || 1,
                precio: t.precio
            }))
        ];
        
        // Calcular el precio total correctamente
        let subtotal = 0;
        componentes.forEach(comp => {
            subtotal += comp.precio * comp.cantidad;
        });
        const iva = subtotal * 0.19;
        const precioTotal = subtotal + iva;
        
        // Deshabilitar el botón mientras se procesa el pedido
        const orderBtn = this;
        orderBtn.disabled = true;
        orderBtn.textContent = 'Procesando...';
        
        fetch('<?= AssetHelper::url('api/custom-coffee/place-order') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                componentes,
                precio_total: precioTotal
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Pedido realizado con éxito');
                window.location.href = '<?= AssetHelper::url('custom-coffee/order-details') ?>/' + data.pedido_id;
            } else {
                alert('Error al realizar el pedido: ' + data.message);
                orderBtn.disabled = false;
                orderBtn.textContent = 'Realizar Pedido';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al realizar el pedido. Por favor, intenta nuevamente.');
            orderBtn.disabled = false;
            orderBtn.textContent = 'Realizar Pedido';
        });
    });
});

function closeModal() {
    document.getElementById('save-recipe-modal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('save-recipe-modal');
    if (event.target === modal) {
        closeModal();
    }
}
</script> 