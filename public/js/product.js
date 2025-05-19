/**
 * Funcionalidad para la página de productos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
    const categoriaButtons = document.querySelectorAll('.categoria-bar button');
    const productosGrid = document.getElementById('productos');
    
    // Agregar event listeners a los botones de categoría
    categoriaButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Actualizar clase activa
            categoriaButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Cargar productos por categoría
            loadProducts(category);
        });
    });
    
    // Agregar event listeners a los botones de agregar al carrito
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('agregar')) {
            const productId = e.target.dataset.id;
            addToCart(productId, 1);
        }
    });
    
    /**
     * Carga productos por categoría
     * @param {string} category Categoría a cargar
     */
    function loadProducts(category) {
        // Mostrar indicador de carga
        productosGrid.innerHTML = '<div class="loading">Cargando productos...</div>';
        
        // URL para la API de productos
        const apiUrl = `api/products?category=${encodeURIComponent(category)}`;
        
        // Realizar solicitud AJAX
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayProducts(data.products);
                } else {
                    showError('Error al cargar productos: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error al conectar con el servidor');
            });
    }
    
    /**
     * Muestra los productos en la interfaz
     * @param {Array} products Lista de productos
     */
    function displayProducts(products) {
        // Limpiar grid
        productosGrid.innerHTML = '';
        
        // Verificar si hay productos
        if (products.length === 0) {
            productosGrid.innerHTML = '<div class="empty-state">No se encontraron productos en esta categoría.</div>';
            return;
        }
        
        // Crear tarjetas de productos
        products.forEach(product => {
            // Preparar nombre de imagen
            const imageName = product.name.toLowerCase().replace(/ /g, '_') + '.jpg';
            
            // Crear elemento de producto
            const productCard = document.createElement('div');
            productCard.className = 'producto-tarjeta';
            productCard.dataset.id = product.id;
            
            // Contenido HTML
            productCard.innerHTML = `
                <div class="producto-imagen">
                    <img src="/img/IMG-P/${imageName}" alt="${product.name}" onerror="this.src='/api/placeholder/300/300'">
                </div>
                <div class="producto-info">
                    <h2>${product.name}</h2>
                    <p class="categoria">${product.category}</p>
                    <p class="precio">$${parseFloat(product.price).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                    <p class="stock">Disponible: ${product.stock} unidades</p>
                    <div class="acciones">
                        <a href="products/detail/${product.id}" class="ver-detalle">Ver Detalle</a>
                        <button class="agregar" data-id="${product.id}">Agregar</button>
                    </div>
                </div>
            `;
            
            // Agregar a la grid
            productosGrid.appendChild(productCard);
        });
    }
    
    /**
     * Muestra un mensaje de error
     * @param {string} message Mensaje de error
     */
    function showError(message) {
        productosGrid.innerHTML = `<div class="error-message">${message}</div>`;
    }
    
    /**
     * Agrega un producto al carrito
     * @param {number} productId ID del producto
     * @param {number} quantity Cantidad a agregar
     */
    function addToCart(productId, quantity) {
        fetch('api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                producto_ID: productId, 
                cantidad: quantity 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Producto agregado al carrito con éxito.');
                
                // Actualizar contador del carrito si existe
                const cartCounter = document.querySelector('.cart-counter');
                if (cartCounter && data.cartCount) {
                    cartCounter.textContent = data.cartCount;
                    cartCounter.style.display = 'block';
                }
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar el producto al carrito.', 'error');
        });
    }
    
    /**
     * Muestra una notificación temporal
     * @param {string} message Mensaje a mostrar
     * @param {string} type Tipo de notificación (success, error)
     */
    function showNotification(message, type = 'success') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Agregar al DOM
        document.body.appendChild(notification);
        
        // Mostrar con efecto
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});/**
 * Funcionalidad para la página de productos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
    const categoriaButtons = document.querySelectorAll('.categoria-bar button');
    const productosGrid = document.getElementById('productos');
    
    // Agregar event listeners a los botones de categoría
    categoriaButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Actualizar clase activa
            categoriaButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Cargar productos por categoría
            loadProducts(category);
        });
    });
    
    // Agregar event listeners a los botones de agregar al carrito
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('agregar')) {
            const productId = e.target.dataset.id;
            addToCart(productId, 1);
        }
    });
    
    /**
     * Carga productos por categoría
     * @param {string} category Categoría a cargar
     */
    function loadProducts(category) {
        // Mostrar indicador de carga
        productosGrid.innerHTML = '<div class="loading">Cargando productos...</div>';
        
        // URL para la API de productos
        const apiUrl = `api/products?category=${encodeURIComponent(category)}`;
        
        // Realizar solicitud AJAX
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayProducts(data.products);
                } else {
                    showError('Error al cargar productos: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error al conectar con el servidor');
            });
    }
    
    /**
     * Muestra los productos en la interfaz
     * @param {Array} products Lista de productos
     */
    function displayProducts(products) {
        // Limpiar grid
        productosGrid.innerHTML = '';
        
        // Verificar si hay productos
        if (products.length === 0) {
            productosGrid.innerHTML = '<div class="empty-state">No se encontraron productos en esta categoría.</div>';
            return;
        }
        
        // Crear tarjetas de productos
        products.forEach(product => {
            // Preparar nombre de imagen
            const imageName = product.name.toLowerCase().replace(/ /g, '_') + '.jpg';
            
            // Crear elemento de producto
            const productCard = document.createElement('div');
            productCard.className = 'producto-tarjeta';
            productCard.dataset.id = product.id;
            
            // Contenido HTML
            productCard.innerHTML = `
                <div class="producto-imagen">
                    <img src="/img/IMG-P/${imageName}" alt="${product.name}" onerror="this.src='/api/placeholder/300/300'">
                </div>
                <div class="producto-info">
                    <h2>${product.name}</h2>
                    <p class="categoria">${product.category}</p>
                    <p class="precio">$${parseFloat(product.price).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</p>
                    <p class="stock">Disponible: ${product.stock} unidades</p>
                    <div class="acciones">
                        <a href="products/detail/${product.id}" class="ver-detalle">Ver Detalle</a>
                        <button class="agregar" data-id="${product.id}">Agregar</button>
                    </div>
                </div>
            `;
            
            // Agregar a la grid
            productosGrid.appendChild(productCard);
        });
    }
    
    /**
     * Muestra un mensaje de error
     * @param {string} message Mensaje de error
     */
    function showError(message) {
        productosGrid.innerHTML = `<div class="error-message">${message}</div>`;
    }
    
    /**
     * Agrega un producto al carrito
     * @param {number} productId ID del producto
     * @param {number} quantity Cantidad a agregar
     */
    function addToCart(productId, quantity) {
        fetch('api/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                producto_ID: productId, 
                cantidad: quantity 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Producto agregado al carrito con éxito.');
                
                // Actualizar contador del carrito si existe
                const cartCounter = document.querySelector('.cart-counter');
                if (cartCounter && data.cartCount) {
                    cartCounter.textContent = data.cartCount;
                    cartCounter.style.display = 'block';
                }
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar el producto al carrito.', 'error');
        });
    }
    
    /**
     * Muestra una notificación temporal
     * @param {string} message Mensaje a mostrar
     * @param {string} type Tipo de notificación (success, error)
     */
    function showNotification(message, type = 'success') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        // Agregar al DOM
        document.body.appendChild(notification);
        
        // Mostrar con efecto
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});