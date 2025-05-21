<?php
// app/views/cart/index.php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';

$title = $title ?? 'Carrito de Compras';
?>

<link rel="stylesheet" href="<?= AssetHelper::css('carro') ?>">

<div class="container">
    <h1 class="mb-4">Carrito de Compras</h1>
    
    <!-- Botones de Deshacer/Rehacer -->
    <div class="action-buttons">
        <button id="undoBtn" class="btn" disabled>
            <i class="fas fa-undo"></i> Deshacer
        </button>
        <button id="redoBtn" class="btn" disabled>
            <i class="fas fa-redo"></i> Rehacer
        </button>
    </div>

    <!-- Mensaje de error -->
    <div id="carrito-error" class="alert alert-danger" style="display: none;"></div>

    <!-- Tabla del Carrito -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cartItems">
                <!-- Los items se cargarán dinámicamente -->
            </tbody>
        </table>
    </div>

    <!-- Sección del Total -->
    <div class="total-section">
        <div id="total"></div>
    </div>

    <!-- Botones de Acción -->
    <div class="cart-actions">
        <a href="<?= AssetHelper::url('productos') ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Seguir Comprando
        </a>
        <button id="checkoutBtn" class="btn btn-success" disabled onclick="finalizarCompra()">
            <i class="fas fa-shopping-cart"></i> Finalizar Compra
        </button>
    </div>
</div>

<script>
    // Mostrar/ocultar mensaje de error
    function mostrarError(mostrar, mensaje = 'Se produjo un error al comunicarse con el servidor. Por favor, intenta nuevamente.') {
        const errorDiv = document.getElementById('carrito-error');
        errorDiv.textContent = mensaje;
        errorDiv.style.display = mostrar ? 'block' : 'none';
    }
    
    // Función para formatear números en formato CLP (con punto como separador de miles)
    function formatearPrecioCLP(numero) {
        return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Función para actualizar el estado de los botones de deshacer/rehacer
    function updateUndoRedoButtons() {
        console.log('Actualizando estado de botones deshacer/rehacer...');
        fetch('<?= AssetHelper::url('cart/history') ?>')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Estado de botones:', data);
                if (data.success) {
                    document.getElementById('undoBtn').disabled = !data.hasUndoActions;
                    document.getElementById('redoBtn').disabled = !data.hasRedoActions;
                }
            })
            .catch(error => {
                console.error('Error al actualizar botones:', error);
            });
    }

    // Función para obtener el carrito del usuario
    function obtenerCarrito() {
        console.log('Obteniendo carrito...');
        mostrarError(false);
        
        fetch('<?= AssetHelper::url('cart/items') ?>')
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
                    mostrarCarrito(data.carrito || []); // Asegurar que carrito sea al menos un array vacío
                    updateUndoRedoButtons(); // Actualizar estado de botones después de obtener el carrito
                } else {
                    mostrarCarrito([]); // Si no hay productos, mostramos el carrito vacío
                    if (data.message) {
                        console.warn('Mensaje del servidor:', data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error al obtener carrito:', error);
                document.getElementById('carrito').innerHTML = '<p>Error al cargar el carrito. Intenta recargar la página.</p>';
                mostrarError(true, 'Error de comunicación con el servidor: ' + error.message);
            });
    }

    // Función para mostrar los productos del carrito
    function mostrarCarrito(carrito) {
        const tbody = document.getElementById('cartItems');
        tbody.innerHTML = '';
        let total = 0;

        if (!carrito || carrito.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No hay productos en el carrito.</td></tr>';
            document.getElementById('total').innerHTML = '';
            document.getElementById('checkoutBtn').disabled = true;
            return;
        }

        carrito.forEach(producto => {
            const subtotal = producto.precio * producto.cantidad;
            total += subtotal;

            const tr = document.createElement('tr');
            tr.setAttribute('data-producto-id', producto.producto_ID);
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <img src="<?= AssetHelper::url('img-p') ?>/${producto.nombre_producto.toLowerCase().replace(/ /g, '_')}.jpg" 
                             alt="${producto.nombre_producto}" 
                             class="me-3" 
                             style="width: 50px; height: 50px; object-fit: cover;"
                             onerror="this.src='<?= AssetHelper::url('img-p') ?>/default.jpg';">
                        <span>${producto.nombre_producto}</span>
                    </div>
                </td>
                <td>$${formatearPrecioCLP(producto.precio)}</td>
                <td>
                    <div class="quantity-controls">
                        <button type="button" class="btn-quantity" onclick="actualizarCantidad(${producto.producto_ID}, ${producto.cantidad - 1})" 
                                ${producto.cantidad <= 1 ? 'disabled' : ''}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="${producto.cantidad}" min="1" 
                               onchange="actualizarCantidad(${producto.producto_ID}, parseInt(this.value) || 1)" 
                               class="quantity-input">
                        <button type="button" class="btn-quantity" onclick="actualizarCantidad(${producto.producto_ID}, ${producto.cantidad + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td>$${formatearPrecioCLP(subtotal)}</td>
                <td>
                    <button class="delete-btn" onclick="eliminarDelCarrito(${producto.producto_ID})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        const iva = total * 0.19;
        const totalConIVA = total + iva;

        document.getElementById('total').innerHTML = `
            <p>Subtotal: $${formatearPrecioCLP(total)}</p>
            <p>IVA (19%): $${formatearPrecioCLP(iva)}</p>
            <p class="total-final">Total: $${formatearPrecioCLP(totalConIVA)}</p>
        `;

        document.getElementById('checkoutBtn').disabled = false;
    }

    // Función para actualizar la cantidad de un producto
    function actualizarCantidad(productoID, nuevaCantidad) {
        if (nuevaCantidad < 1) {
            eliminarDelCarrito(productoID);
            return;
        }

        // Mostrar indicador de carga
        const productoElement = document.querySelector(`tr[data-producto-id="${productoID}"]`);
        if (productoElement) {
            productoElement.style.opacity = '0.5';
        }

        fetch('<?= AssetHelper::url('cart/add') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                producto_ID: productoID, 
                cantidad: nuevaCantidad,
                actualizar: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                obtenerCarrito();
                updateUndoRedoButtons(); // Actualizar estado de botones después de actualizar cantidad
            } else {
                mostrarError(true, data.message || 'Error al actualizar la cantidad');
                if (productoElement) {
                    productoElement.style.opacity = '1';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError(true, 'Error al actualizar la cantidad');
            if (productoElement) {
                productoElement.style.opacity = '1';
            }
        });
    }

    // Función para eliminar un producto del carrito
    function eliminarDelCarrito(productoID) {
        console.log('Eliminando producto:', productoID);
        mostrarError(false);
        
        fetch('<?= AssetHelper::url('cart/remove') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ producto_ID: productoID })
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
                obtenerCarrito();
                updateUndoRedoButtons(); // Actualizar estado de botones después de eliminar
            } else {
                alert('Error al eliminar el producto del carrito: ' + (data.message || 'Error desconocido'));
                mostrarError(true, 'Error al eliminar el producto: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error al eliminar producto:', error);
            mostrarError(true, 'Error de comunicación con el servidor: ' + error.message);
        });
    }

    // Función para finalizar la compra
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

    document.addEventListener('DOMContentLoaded', function() {
        const undoBtn = document.getElementById('undoBtn');
        const redoBtn = document.getElementById('redoBtn');

        // Función para deshacer última acción
        undoBtn.addEventListener('click', function() {
            fetch('<?= AssetHelper::url('cart/undo') ?>', {
                method: 'POST'
            })
            .then(response => response.json())
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

        // Función para rehacer última acción
        redoBtn.addEventListener('click', function() {
            fetch('<?= AssetHelper::url('cart/redo') ?>', {
                method: 'POST'
            })
            .then(response => response.json())
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