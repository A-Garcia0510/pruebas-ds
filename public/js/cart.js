// public/js/cart.js

document.addEventListener('DOMContentLoaded', function() {
    // Cargar el contenido del carrito en la página
    function cargarCarrito() {
        fetch('/api/cart')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    actualizarInterfazCarrito(data);
                } else {
                    console.error('Error al cargar el carrito:', data.message);
                }
            })
            .catch(error => {
                console.error('Error de comunicación con el servidor:', error);
                alert('Error al cargar el carrito. Intenta recargar la página.');
            });
    }

    // Función para actualizar la interfaz del carrito con los datos recibidos
    function actualizarInterfazCarrito(data) {
        // Actualizar contador del carrito en el header si existe
        const contadorCarrito = document.querySelector('.cart-count');
        if (contadorCarrito) {
            contadorCarrito.textContent = data.cartCount;
        }
        
        // Si estamos en la página del carrito, actualizar la lista de productos
        const carritoContainer = document.querySelector('.carrito-container');
        if (carritoContainer) {
            // Aquí podrías actualizar dinámicamente el contenido del carrito
            // Por ahora simplemente recargamos la página
            if (data.items && data.items.length === 0) {
                window.location.reload();
            }
        }
    }

    // Agregar producto al carrito
    function agregarAlCarrito(productoId, cantidad) {
        fetch('/api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                producto_ID: productoId, 
                cantidad: cantidad 
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Producto agregado al carrito con éxito.');
                // Actualizar contador del carrito en el header
                const contadorCarrito = document.querySelector('.cart-count');
                if (contadorCarrito) {
                    contadorCarrito.textContent = data.cartCount;
                }
            } else {
                alert('Error al agregar producto: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el producto al carrito.');
        });
    }

    // Asignar eventos a botones de agregar al carrito en la lista de productos
    const botonesAgregar = document.querySelectorAll('.agregar');
    if (botonesAgregar) {
        botonesAgregar.forEach(btn => {
            btn.addEventListener('click', function() {
                const productoId = this.dataset.id;
                agregarAlCarrito(productoId, 1);
            });
        });
    }

    // Inicializar: cargar carrito si estamos en la página del carrito
    if (window.location.pathname.includes('/cart') || window.location.pathname.includes('/carrito')) {
        cargarCarrito();
    }
});