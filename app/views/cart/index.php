<?php
// app/views/cart/index.php

/**
 * Vista para el carrito de compras
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>
<link rel="stylesheet" href="<?= AssetHelper::css('carro') ?>">

<div class="page-title">
    <h1>Carrito de Compras</h1>
    <a href="<?= AssetHelper::url('products') ?>" class="volver-link">
        <i>←</i> Volver a la tienda
    </a>
</div>

<div class="carrito-container">
    <?php if (empty($cartItems)): ?>
        <div class="carrito-vacio">
            <h2>Tu carrito está vacío</h2>
            <p>Parece que aún no has añadido productos a tu carrito.</p>
            <a href="<?= AssetHelper::url('products') ?>" class="btn-primary">Ver productos</a>
        </div>
    <?php else: ?>
        <div class="carrito-items">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr data-id="<?= $item->getProductId() ?>">
                            <td><?= htmlspecialchars($item->getProductName()) ?></td>
                            <td>$<?= number_format($item->getProductPrice(), 0, ',', '.') ?></td>
                            <td>
                                <div class="cantidad-controles">
                                    <button type="button" class="qty-btn decrementar" data-id="<?= $item->getProductId() ?>">-</button>
                                    <span class="cantidad"><?= $item->getQuantity() ?></span>
                                    <button type="button" class="qty-btn incrementar" data-id="<?= $item->getProductId() ?>">+</button>
                                </div>
                            </td>
                            <td>$<?= number_format($item->getSubtotal(), 0, ',', '.') ?></td>
                            <td>
                                <button class="btn-eliminar" data-id="<?= $item->getProductId() ?>">
                                    <img src="<?= AssetHelper::img('trash.png') ?>" alt="Eliminar">
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="carrito-resumen">
            <h3>Resumen del Pedido</h3>
            <div class="resumen-detalle">
                <div class="detalle-linea">
                    <span>Subtotal</span>
                    <span>$<?= number_format($cartTotal, 0, ',', '.') ?></span>
                </div>
                <div class="detalle-linea">
                    <span>Envío</span>
                    <span>Calculado en el checkout</span>
                </div>
                <div class="detalle-linea total">
                    <span>Total</span>
                    <span>$<?= number_format($cartTotal, 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="acciones-carrito">
                <button id="vaciar-carrito" class="btn-secondary">Vaciar Carrito</button>
                <button id="proceder-compra" class="btn-primary">Proceder al Pago</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnEliminar = document.querySelectorAll('.btn-eliminar');
    const btnDecrementar = document.querySelectorAll('.decrementar');
    const btnIncrementar = document.querySelectorAll('.incrementar');
    const btnVaciarCarrito = document.getElementById('vaciar-carrito');
    const btnProcederCompra = document.getElementById('proceder-compra');
    
    // Función para eliminar un producto del carrito
    function eliminarProducto(id) {
        fetch('<?= AssetHelper::url('api/cart/remove') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ producto_ID: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar los cambios
                location.reload();
            } else {
                alert('Error al eliminar producto: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de comunicación con el servidor: ' + error);
        });
    }
    
    // Función para actualizar la cantidad de un producto
    function actualizarCantidad(id, cantidad) {
        fetch('<?= AssetHelper::url('api/cart/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                producto_ID: id, 
                cantidad: cantidad 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar los cambios
                location.reload();
            } else {
                alert('Error al actualizar cantidad: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de comunicación con el servidor: ' + error);
        });
    }
    
    // Función para vaciar el carrito
    function vaciarCarrito() {
        if (confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
            fetch('<?= AssetHelper::url('api/cart/clear') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para mostrar los cambios
                    location.reload();
                } else {
                    alert('Error al vaciar carrito: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de comunicación con el servidor: ' + error);
            });
        }
    }
    
    // Asignar eventos a botones de eliminar
    btnEliminar.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            eliminarProducto(id);
        });
    });
    
    // Asignar eventos a botones de decrementar
    btnDecrementar.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const cantidadElement = this.parentNode.querySelector('.cantidad');
            let cantidad = parseInt(cantidadElement.textContent);
            if (cantidad > 1) {
                actualizarCantidad(id, -1); // Restar 1
            }
        });
    });
    
    // Asignar eventos a botones de incrementar
    btnIncrementar.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            actualizarCantidad(id, 1); // Sumar 1
        });
    });
    
    // Asignar evento a botón de vaciar carrito
    if (btnVaciarCarrito) {
        btnVaciarCarrito.addEventListener('click', vaciarCarrito);
    }
    
    // Asignar evento a botón de proceder al pago
    if (btnProcederCompra) {
        btnProcederCompra.addEventListener('click', function() {
            window.location.href = '<?= AssetHelper::url('checkout') ?>';
        });
    }
});
</script>