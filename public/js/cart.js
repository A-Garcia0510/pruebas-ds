// public/js/cart.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de carrito inicializado');
    
    // Base URL para peticiones AJAX
    const baseUrl = window.location.origin + '/pruebas-ds/public';
    console.log('URL base:', baseUrl);
    
    // URL para obtener items del carrito
    const cartUrl = `${baseUrl}/cart/items`;
    console.log('URL del carrito:', cartUrl);
    
    // Referencias a elementos DOM
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const btnCheckout = document.getElementById('btn-checkout');
    const btnUndo = document.getElementById('btn-undo');
    const btnRedo = document.getElementById('btn-redo');
    
    // Cargar datos del carrito
    console.log('Cargando datos del carrito...');
    fetch(cartUrl, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Respuesta del servidor:', response);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos del carrito recibidos:', data);
        if (data.success) {
            renderCart(data.carrito, data.total);
            updateHistoryButtons();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error al cargar el carrito:', error);
        showError('Error al cargar el carrito');
    });
    
    /**
     * Renderiza los items del carrito
     * @param {Array} items - Items del carrito
     * @param {number} total - Total del carrito
     */
    function renderCart(items, total) {
        if (!cartItems) return;
        
        cartItems.innerHTML = '';
        
        if (items.length === 0) {
            cartItems.innerHTML = '<div class="empty-cart">El carrito está vacío</div>';
            if (cartTotal) cartTotal.textContent = '$0';
            if (btnCheckout) btnCheckout.disabled = true;
            return;
        }
        
        items.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <div class="item-info">
                    <h3>${item.nombre_producto}</h3>
                    <p class="price">$${formatNumber(item.precio)}</p>
                </div>
                <div class="item-quantity">
                    <button class="btn-quantity" data-action="decrease" data-id="${item.producto_ID}">-</button>
                    <span>${item.cantidad}</span>
                    <button class="btn-quantity" data-action="increase" data-id="${item.producto_ID}">+</button>
                </div>
                <div class="item-subtotal">
                    <p>$${formatNumber(item.subtotal)}</p>
                </div>
                <button class="btn-remove" data-id="${item.producto_ID}">×</button>
            `;
            cartItems.appendChild(itemElement);
        });
        
        if (cartTotal) cartTotal.textContent = `$${formatNumber(total)}`;
        if (btnCheckout) btnCheckout.disabled = false;
        
        // Agregar event listeners a los botones
        addCartEventListeners();
    }
    
    /**
     * Agrega los event listeners a los botones del carrito
     */
    function addCartEventListeners() {
        // Botones de cantidad
        document.querySelectorAll('.btn-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                const productId = this.dataset.id;
                const currentQuantity = parseInt(this.parentElement.querySelector('span').textContent);
                const newQuantity = action === 'increase' ? currentQuantity + 1 : currentQuantity - 1;
                
                if (newQuantity > 0) {
                    updateCartItem(productId, newQuantity);
                }
            });
        });
        
        // Botones de eliminar
        document.querySelectorAll('.btn-remove').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                removeCartItem(productId);
            });
        });
    }
    
    /**
     * Actualiza la cantidad de un item en el carrito
     * @param {number} productId - ID del producto
     * @param {number} quantity - Nueva cantidad
     */
    function updateCartItem(productId, quantity) {
        fetch(`${baseUrl}/cart/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                producto_ID: productId,
                cantidad: quantity,
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
                loadCartData();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al actualizar el carrito');
        });
    }
    
    /**
     * Elimina un item del carrito
     * @param {number} productId - ID del producto
     */
    function removeCartItem(productId) {
        fetch(`${baseUrl}/cart/remove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                loadCartData();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al eliminar el producto');
        });
    }
    
    /**
     * Carga los datos actualizados del carrito
     */
    function loadCartData() {
        fetch(cartUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                renderCart(data.carrito, data.total);
                updateHistoryButtons();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al cargar el carrito');
        });
    }
    
    /**
     * Actualiza el estado de los botones de historial
     */
    function updateHistoryButtons() {
        fetch(`${baseUrl}/cart/history`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                if (btnUndo) btnUndo.disabled = !data.hasUndoActions;
                if (btnRedo) btnRedo.disabled = !data.hasRedoActions;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    /**
     * Muestra un mensaje de error
     * @param {string} message - Mensaje de error
     */
    function showError(message) {
        if (cartItems) {
            cartItems.innerHTML = `<div class="error-message">${message}</div>`;
        }
    }
    
    /**
     * Formatea números con separador de miles
     * @param {number} number - Número a formatear
     * @return {string} Número formateado
     */
    function formatNumber(number) {
        return new Intl.NumberFormat('es-CL').format(number);
    }
    
    // Configurar eventos para los botones de historial
    if (btnUndo) {
        btnUndo.addEventListener('click', function() {
            fetch(`${baseUrl}/cart/undo`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
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
                    loadCartData();
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error al deshacer la acción');
            });
        });
    }
    
    if (btnRedo) {
        btnRedo.addEventListener('click', function() {
            fetch(`${baseUrl}/cart/redo`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
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
                    loadCartData();
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error al rehacer la acción');
            });
        });
    }
    
    // Configurar evento para el botón de checkout
    if (btnCheckout) {
        btnCheckout.addEventListener('click', function() {
            fetch(`${baseUrl}/cart/checkout`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
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
                    alert('Compra realizada con éxito');
                    window.location.href = `${baseUrl}/dashboard`;
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error al procesar la compra');
            });
        });
    }
});