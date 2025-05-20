<?php
// app/views/cart/index.php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>
<link rel="stylesheet" href="<?= AssetHelper::css('carro') ?>">

<div class="container">
    <h1>Carrito de Compras</h1>
    
    <div id="carrito-error" class="alert alert-danger" style="display: none;">
        Se produjo un error al comunicarse con el servidor. Por favor, intenta nuevamente.
    </div>
    
    <div id="carrito-contenedor">
        <div id="carrito">
            <p>Cargando carrito...</p>
        </div>
        <div class="total" id="total"></div>
    </div>
    
    <div class="cart-actions">
        <button id="volver" class="btn btn-secondary" onclick="window.location.href='<?= AssetHelper::url('productos') ?>'">Volver a la Tienda</button>
        <button id="finalizarCompra" class="btn btn-primary" onclick="finalizarCompra()">Finalizar Compra</button>
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
        const carritoDiv = document.getElementById('carrito');
        carritoDiv.innerHTML = '';
        let total = 0;

        if (!carrito || carrito.length === 0) {
            carritoDiv.innerHTML = '<p>No hay productos en el carrito.</p>';
            document.getElementById('total').innerHTML = '';
            return;
        }

        carrito.forEach(producto => {
            const subtotal = producto.precio * producto.cantidad;
            total += subtotal;

            // Usar una imagen por defecto si el nombre del producto está vacío
            let nombre_imagen;
            if (producto.nombre_producto) {
                nombre_imagen = producto.nombre_producto.toLowerCase().replace(/ /g, '_') + '.jpg';
            } else {
                nombre_imagen = 'default.jpg'; // Imagen por defecto
            }

            carritoDiv.innerHTML += `
                <div class="producto" data-id="${producto.producto_ID}">
                    <img src="<?= AssetHelper::url('img-p') ?>/${nombre_imagen}" alt="${producto.nombre_producto || 'Producto'}" onerror="this.src='<?= AssetHelper::url('img-p') ?>/default.jpg';" />
                    <h2>${producto.nombre_producto || 'Producto sin nombre'}</h2>
                    <p>Precio: $${formatearPrecioCLP(producto.precio || 0)}</p>
                    <div class="cantidad">
                        <button onclick="actualizarCantidad(${producto.producto_ID}, ${producto.cantidad - 1})" ${producto.cantidad <= 1 ? 'disabled' : ''}>-</button>
                        <span>${producto.cantidad || 0}</span>
                        <button onclick="actualizarCantidad(${producto.producto_ID}, ${producto.cantidad + 1})">+</button>
                    </div>
                    <button class="eliminar" onclick="eliminarDelCarrito(${producto.producto_ID})">Eliminar</button>
                </div>
            `;
        });

        const iva = total * 0.19; // IVA del 19%
        const totalConIVA = total + iva;

        document.getElementById('total').innerHTML = `
            <div class="resumen-total">
                <p>Subtotal: $${formatearPrecioCLP(total)}</p>
                <p>IVA (19%): $${formatearPrecioCLP(iva)}</p>
                <p class="total-final">Total: $${formatearPrecioCLP(totalConIVA)}</p>
            </div>
        `;
    }

    // Función para actualizar la cantidad de un producto
    function actualizarCantidad(productoID, nuevaCantidad) {
        if (nuevaCantidad < 1) {
            eliminarDelCarrito(productoID);
            return;
        }

        // Mostrar indicador de carga
        const productoElement = document.querySelector(`.producto[data-id="${productoID}"]`);
        if (productoElement) {
            productoElement.style.opacity = '0.5';
        }

        fetch('<?= AssetHelper::url('cart/add') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                producto_ID: productoID, 
                cantidad: nuevaCantidad 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                obtenerCarrito();
            } else {
                mostrarError(true, data.message || 'Error al actualizar la cantidad');
                // Restaurar opacidad
                if (productoElement) {
                    productoElement.style.opacity = '1';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError(true, 'Error al actualizar la cantidad');
            // Restaurar opacidad
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
                obtenerCarrito(); // Recargar el carrito después de eliminar
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
        
        // Verifica si el carrito está vacío
        const carritoDiv = document.getElementById('carrito');
        if (carritoDiv.children.length === 0 || (carritoDiv.children[0].tagName === 'P' && carritoDiv.children[0].textContent.includes('No hay productos'))) {
            alert('No puedes finalizar la compra porque el carrito está vacío.');
            return; // Detenemos la ejecución si el carrito está vacío
        }

        fetch('<?= AssetHelper::url('cart/checkout') ?>', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
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
                alert('Compra realizada con éxito.');
                window.location.href = '<?= AssetHelper::url('productos') ?>';
            } else {
                alert('Error al finalizar la compra: ' + (data.message || 'Error desconocido'));
                mostrarError(true, 'Error al finalizar la compra: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error al finalizar compra:', error);
            mostrarError(true, 'Error de comunicación con el servidor: ' + error.message);
        });
    }

    // Llamada inicial para cargar los productos en el carrito
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado, obteniendo carrito...');
        obtenerCarrito();
    });
</script>