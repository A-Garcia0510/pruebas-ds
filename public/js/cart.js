// public/js/cart.js

document.addEventListener('DOMContentLoaded', function() {
    const CART_API_URL = '/api/cart';
    
    // Log para depuración
    console.log('Script de carrito inicializado');
    
    // Cargar el contenido del carrito en la página
    function cargarCarrito() {
        console.log('Cargando datos del carrito...');
        
        fetch(CART_API_URL)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos del carrito recibidos:', data);
                
                if (data.success) {
                    actualizarInterfazCarrito(data);
                } else {
                    console.error('Error al cargar el carrito:', data.message);
                    if (data.message && data.message.includes('no logueado')) {
                        alert('Debes iniciar sesión para ver tu carrito');
                    }
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
        if (contadorCarrito && data.cartCount !== undefined) {
            console.log('Actualizando contador de carrito:', data.cartCount);
            contadorCarrito.textContent = data.cartCount;
        }
        
        // Si estamos en la página del carrito, actualizar la lista de productos
        const carritoContainer = document.querySelector('.carrito-container');
        if (carritoContainer) {
            console.log('Estamos en la página del carrito, actualizando interfaz');
            
            // Si tenemos un contenedor de productos vacío y hay productos, recargamos
            const carritoVacio = document.querySelector('.carrito-vacio');
            const carritoItems = document.querySelector('.carrito-items');
            
            if ((carritoVacio && data.items && data.items.length > 0) || 
                (carritoItems && (!data.items || data.items.length === 0))) {
                console.log('Estado del carrito cambió, recargando página');
                window.location.reload();
                return;
            }
            
            // Si ya tenemos la tabla de productos, actualizar subtotales
            if (carritoItems) {
                // Actualizar totales
                const totalSpans = document.querySelectorAll('.detalle-linea.total span:last-child');
                if (totalSpans && totalSpans.length > 0) {
                    totalSpans.forEach(span => {
                        span.textContent = '$' + number_format(data.total, 0, ',', '.');
                    });
                }
            }
        }
    }

    // Agregar producto al carrito
    function agregarAlCarrito(productoId, cantidad) {
        console.log(`Agregando producto ID: ${productoId}, cantidad: ${cantidad}`);
        
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
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                mostrarNotificacion('Producto agregado al carrito con éxito.');
                
                // Actualizar contador del carrito en el header
                const contadorCarrito = document.querySelector('.cart-count');
                if (contadorCarrito && data.cartCount !== undefined) {
                    contadorCarrito.textContent = data.cartCount;
                } else {
                    // Si no recibimos el contador, recargar datos del carrito
                    cargarCarrito();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el producto al carrito.');
        });
    }

    // Mostrar notificación flotante
    function mostrarNotificacion(mensaje) {
        // Comprobar si ya existe una notificación
        let notificacion = document.querySelector('.notificacion-flotante');
        
        if (!notificacion) {
            // Crear elemento de notificación
            notificacion = document.createElement('div');
            notificacion.className = 'notificacion-flotante';
            document.body.appendChild(notificacion);
            
            // Estilos para la notificación
            notificacion.style.position = 'fixed';
            notificacion.style.bottom = '20px';
            notificacion.style.right = '20px';
            notificacion.style.backgroundColor = '#4CAF50';
            notificacion.style.color = 'white';
            notificacion.style.padding = '12px 20px';
            notificacion.style.borderRadius = '4px';
            notificacion.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
            notificacion.style.zIndex = '1000';
            notificacion.style.transition = 'opacity 0.5s ease-in-out';
        }
        
        // Actualizar mensaje y mostrar
        notificacion.textContent = mensaje;
        notificacion.style.opacity = '1';
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notificacion.style.opacity = '0';
            setTimeout(() => {
                if (notificacion.parentNode) {
                    notificacion.parentNode.removeChild(notificacion);
                }
            }, 500);
        }, 3000);
    }

    // Helper para formatear números como moneda
    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    // Asignar eventos a botones de agregar al carrito en la lista de productos
    const botonesAgregar = document.querySelectorAll('.agregar-al-carrito, .agregar');
    if (botonesAgregar && botonesAgregar.length > 0) {
        console.log(`Encontrados ${botonesAgregar.length} botones de agregar al carrito`);
        
        botonesAgregar.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productoId = this.dataset.id;
                console.log(`Click en botón de agregar para producto ID: ${productoId}`);
                agregarAlCarrito(productoId, 1);
            });
        });
    } else {
        console.log('No se encontraron botones de agregar al carrito');
    }

    // Inicializar: cargar carrito en todas las páginas para actualizar contador
    cargarCarrito();

    // Si estamos en la página de detalle del producto, agregar eventos a los controles de cantidad
    const detalleProducto = document.querySelector('.detalle-producto');
    if (detalleProducto) {
        console.log('Estamos en la página de detalle del producto');
        
        const btnAgregarDetalle = document.querySelector('.btn-agregar-carrito');
        const inputCantidad = document.querySelector('input.cantidad');
        
        if (btnAgregarDetalle && inputCantidad) {
            btnAgregarDetalle.addEventListener('click', function(e) {
                e.preventDefault();
                const productoId = this.dataset.id;
                const cantidad = parseInt(inputCantidad.value) || 1;
                agregarAlCarrito(productoId, cantidad);
            });
        }
    }
});