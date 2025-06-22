<?php
// app/views/cart/index.php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';

$title = $title ?? 'Carrito de Compras';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('carro') ?>">

<div class="cart-container">
    <h1 class="cart-title">Carrito de Compras</h1>
    
    <div class="cart-actions-top">
        <button id="undoBtn" class="cart-action-btn" disabled>
            <i class="fas fa-undo"></i> Deshacer
        </button>
        <button id="redoBtn" class="cart-action-btn" disabled>
            <i class="fas fa-redo"></i> Rehacer
        </button>
    </div>

    <div id="carrito-error" class="cart-error" style="display: none;"></div>

    <div class="cart-layout">
        <div class="cart-items-panel">
            <div class="cart-items-header">
                <div class="header-product">Producto</div>
                <div class="header-price">Precio</div>
                <div class="header-quantity">Cantidad</div>
                <div class="header-subtotal">Subtotal</div>
                <div class="header-actions">Acciones</div>
            </div>
            <div id="cartItems" class="cart-items-body">
                <!-- Los items se cargarán dinámicamente -->
            </div>
        </div>

        <div class="cart-summary-panel">
            <div class="cart-summary">
                <h3 class="summary-title"><i class="fas fa-calculator"></i> Resumen</h3>
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span id="subtotal">$0</span>
                </div>
                <div class="summary-item">
                    <span>IVA (19%)</span>
                    <span id="iva">$0</span>
                </div>
                <div class="summary-item total">
                    <span>Total</span>
                    <span id="total">$0</span>
                </div>
                <button id="checkoutBtn" class="btn-checkout" disabled onclick="finalizarCompra()">
                    <i class="fas fa-shopping-cart"></i> Finalizar Compra
                </button>
                <a href="<?= AssetHelper::url('productos') ?>" class="btn-continue">
                    <i class="fas fa-arrow-left"></i> Seguir Comprando
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales
    let cartHistory = [];
    let currentHistoryIndex = -1;

    // Función para formatear precios en CLP
    function formatearPrecioCLP(precio) {
        return new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP',
            minimumFractionDigits: 0
        }).format(precio);
    }

    // Función para mostrar/ocultar errores
    function mostrarError(mostrar, mensaje = '') {
        const errorDiv = document.getElementById('carrito-error');
        if (mostrar) {
        errorDiv.textContent = mensaje;
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    }
    
    // Función para obtener el carrito
    function obtenerCarrito() {
        fetch('<?= AssetHelper::url('cart/items') ?>', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderizarCarrito(data.carrito);
                actualizarResumen(data.summary);
                } else {
                mostrarError(true, data.message || 'Error al obtener el carrito');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError(true, 'Error de comunicación con el servidor');
        });
                }

    // Función para renderizar el carrito
    function renderizarCarrito(items) {
        const cartBody = document.getElementById('cartItems');
        const checkoutBtn = document.getElementById('checkoutBtn');

        if (!items || items.length === 0) {
            cartBody.innerHTML = '<div class="cart-empty-message">No hay productos en el carrito</div>';
            checkoutBtn.disabled = true;
            return;
        }

        checkoutBtn.disabled = false;
        
        cartBody.innerHTML = items.map(item => `
            <div class="cart-item" data-product-id="${item.producto_ID}">
                <div class="product-info">
                    <img src="<?= AssetHelper::url('IMG-P/') ?>${item.nombre_producto.toLowerCase().replace(/\s+/g, '_')}.jpg" 
                         alt="${item.nombre_producto}" 
                         onerror="this.src='<?= AssetHelper::url('img/carro.png') ?>'">
                    <span class="product-name">${item.nombre_producto}</span>
                </div>
                
                <div class="price" data-label="Precio">${formatearPrecioCLP(item.precio)}</div>
                
                <div class="quantity-controls" data-label="Cantidad">
                    <button type="button" class="btn-quantity" onclick="cambiarCantidad(${item.producto_ID}, ${item.cantidad - 1})">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="quantity">${item.cantidad}</span>
                    <button type="button" class="btn-quantity" onclick="cambiarCantidad(${item.producto_ID}, ${item.cantidad + 1})">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <div class="subtotal" data-label="Subtotal">${formatearPrecioCLP(item.subtotal)}</div>
                
                <div class="actions">
                    <button type="button" class="btn-remove" onclick="eliminarProducto(${item.producto_ID})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Función para actualizar el resumen
    function actualizarResumen(summary) {
        document.getElementById('subtotal').textContent = formatearPrecioCLP(summary.subtotal);
        document.getElementById('iva').textContent = formatearPrecioCLP(summary.iva);
        document.getElementById('total').textContent = formatearPrecioCLP(summary.total);
    }

    // Función para cambiar cantidad
    function cambiarCantidad(productId, nuevaCantidad) {
        if (nuevaCantidad <= 0) {
            eliminarProducto(productId);
            return;
        }

        fetch('<?= AssetHelper::url('cart/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                producto_ID: productId,
                cantidad: nuevaCantidad,
                actualizar: true
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                obtenerCarrito();
            } else {
                mostrarError(true, data.message || 'Error al actualizar cantidad');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError(true, 'Error al actualizar cantidad');
        });
    }

    // Función para eliminar producto
    function eliminarProducto(productId) {
        fetch('<?= AssetHelper::url('cart/remove') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                producto_ID: productId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                obtenerCarrito();
            } else {
                mostrarError(true, data.message || 'Error al eliminar producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError(true, 'Error al eliminar producto');
        });
    }

    // Función para finalizar compra
    function finalizarCompra() {
        console.log('Finalizando compra...');
        mostrarError(false);
        
        // Verificar si el carrito está vacío
        const tbody = document.getElementById('cartItems');
        if (!tbody.children.length || (tbody.children[0].tagName === 'TR' && tbody.children[0].textContent.includes('No hay productos'))) {
            alert('No puedes finalizar la compra porque el carrito está vacío.');
            return;
        }

        // Mostrar indicador de carga
        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        fetch('<?= AssetHelper::url('cart/checkout') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Respuesta recibida:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            if (data.success) {
                alert('¡Compra realizada con éxito!');
                window.location.href = '<?= AssetHelper::url('productos') ?>';
            } else {
                mostrarError(true, data.message || 'Error al finalizar la compra');
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Finalizar Compra';
            }
        })
        .catch(error => {
            console.error('Error al finalizar compra:', error);
            mostrarError(true, 'Error de comunicación con el servidor: ' + error.message);
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Finalizar Compra';
        });
    }

    // Función para actualizar botones de deshacer/rehacer
    function updateUndoRedoButtons() {
        fetch('<?= AssetHelper::url('cart/history') ?>', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const undoBtn = document.getElementById('undoBtn');
                const redoBtn = document.getElementById('redoBtn');
                
                undoBtn.disabled = !data.hasUndoActions;
                redoBtn.disabled = !data.hasRedoActions;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Botones de deshacer/rehacer
        const undoBtn = document.getElementById('undoBtn');
        const redoBtn = document.getElementById('redoBtn');

        undoBtn.addEventListener('click', function() {
            if (this.disabled) return;
            
            fetch('<?= AssetHelper::url('cart/undo') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    obtenerCarrito();
                    updateUndoRedoButtons();
                } else {
                    mostrarError(true, data.message || 'Error al deshacer la acción');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError(true, 'Error al deshacer la acción');
            });
        });

        redoBtn.addEventListener('click', function() {
            if (this.disabled) return;
            
            fetch('<?= AssetHelper::url('cart/redo') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    obtenerCarrito();
                    updateUndoRedoButtons();
                } else {
                    mostrarError(true, data.message || 'Error al rehacer la acción');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError(true, 'Error al rehacer la acción');
            });
        });

        // Cargar estado inicial
        obtenerCarrito();
        updateUndoRedoButtons();
    });
</script>