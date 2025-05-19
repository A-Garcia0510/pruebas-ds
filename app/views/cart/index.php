<?php
// app/views/cart/index.php
// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
?>
<link rel="stylesheet" href="<?= AssetHelper::css('carro') ?>">

<div class="container">
    <h1>Carrito de Compras</h1>
    
    <div id="carrito-contenedor">
        <div id="carrito"></div>
        <div class="total" id="total"></div>
    </div>
    
    <div class="cart-actions">
        <button id="volver" class="btn btn-secondary" onclick="window.location.href='<?= AssetHelper::url('productos') ?>'">Volver a la Tienda</button>
        <button id="finalizarCompra" class="btn btn-primary" onclick="finalizarCompra()">Finalizar Compra</button>
    </div>
</div>

<script>
    // Función para formatear números en formato CLP (con punto como separador de miles)
    function formatearPrecioCLP(numero) {
        return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Función para obtener el carrito del usuario
    function obtenerCarrito() {
        fetch('<?= AssetHelper::url('cart/items') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarCarrito(data.carrito);
                } else {
                    mostrarCarrito([]); // Si no hay productos, mostramos el carrito vacío
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de comunicación con el servidor.');
            });
    }

    // Función para mostrar los productos del carrito
    function mostrarCarrito(carrito) {
        const carritoDiv = document.getElementById('carrito');
        carritoDiv.innerHTML = '';
        let total = 0;

        if (carrito.length === 0) {
            carritoDiv.innerHTML = '<p>No hay productos en el carrito.</p>';
            document.getElementById('total').innerHTML = '';
            return;
        }

        carrito.forEach(producto => {
            const subtotal = producto.precio * producto.cantidad;
            total += subtotal;

            const nombre_imagen = producto.nombre_producto.toLowerCase().replace(/ /g, '_') + '.jpg'; // Generamos el nombre de la imagen basado en el nombre del producto.

            carritoDiv.innerHTML += `
                <div class="producto">
                    <img src="<?= AssetHelper::url('img-p') ?>/${nombre_imagen}" alt="${producto.nombre_producto}" width="100" />
                    <h2>${producto.nombre_producto}</h2>
                    <p>Precio: $${formatearPrecioCLP(producto.precio)}</p>
                    <p>Cantidad: ${producto.cantidad}</p>
                    <button class="btn btn-danger" onclick="eliminarDelCarrito(${producto.producto_ID})">Eliminar</button>
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

    // Función para eliminar un producto del carrito
    function eliminarDelCarrito(productoID) {
        fetch('<?= AssetHelper::url('cart/remove') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ producto_ID: productoID })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                obtenerCarrito(); // Recargar el carrito después de eliminar
            } else {
                alert('Error al eliminar el producto del carrito: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de comunicación con el servidor.');
        });
    }

    // Función para finalizar la compra
    function finalizarCompra() {
        // Verifica si el carrito está vacío
        const carrito = document.getElementById('carrito').children.length;
        if (carrito === 0 || (document.getElementById('carrito').children[0].tagName === 'P' && document.getElementById('carrito').children[0].textContent === 'No hay productos en el carrito.')) {
            alert('No puedes finalizar la compra porque el carrito está vacío.');
            return; // Detenemos la ejecución si el carrito está vacío
        }

        fetch('<?= AssetHelper::url('cart/checkout') ?>', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Compra realizada con éxito.');
                    window.location.href = '<?= AssetHelper::url('productos') ?>';
                } else {
                    alert('Error al finalizar la compra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de comunicación con el servidor.');
            });
    }

    // Llamada inicial para cargar los productos en el carrito
    document.addEventListener('DOMContentLoaded', function() {
        obtenerCarrito();
    });
</script>