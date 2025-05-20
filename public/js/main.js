/**
 * Funcionalidad principal para la sección de productos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const btnTodos = document.getElementById('btn-todos');
    const categoriaButtons = document.querySelectorAll('.categoria-bar button');
    const productosGrid = document.getElementById('productos');
    
    // Base URL para peticiones AJAX
    const baseUrl = window.location.pathname.includes('/public/') 
        ? window.location.pathname.split('/public/')[0] + '/public'
        : '';
    
    /**
     * Función para cargar productos según la categoría seleccionada
     * @param {string} category - Categoría a filtrar
     */
    function loadProductsByCategory(category) {
        // Mostrar estado de carga
        productosGrid.innerHTML = '<div class="loading">Cargando productos...</div>';
        
        // Marcar el botón activo
        categoriaButtons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.category === category) {
                btn.classList.add('active');
            }
        });
        
        // Hacer petición AJAX para obtener productos
        fetch(`${baseUrl}/api/products?category=${category}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar grid de productos
                    productosGrid.innerHTML = '';
                    
                    if (data.products.length === 0) {
                        productosGrid.innerHTML = '<div class="empty-state">No se encontraron productos en esta categoría.</div>';
                        return;
                    }
                    
                    // Renderizar productos
                    data.products.forEach(product => {
                        const productCard = document.createElement('div');
                        productCard.className = 'producto-tarjeta';
                        productCard.dataset.id = product.id;
                        
                        productCard.innerHTML = `
                            <div class="producto-info">
                                <h2>${product.name}</h2>
                                <p class="categoria">${product.category}</p>
                                <p class="precio">$${formatNumber(product.price)}</p>
                                <p class="stock">Disponible: ${product.stock} unidades</p>
                                <div class="acciones">
                                    <a href="${baseUrl}/products/detail/${product.id}" class="ver-detalle">Ver Detalle</a>
                                    <button class="agregar" data-id="${product.id}">Agregar</button>
                                </div>
                            </div>
                        `;
                        
                        productosGrid.appendChild(productCard);
                    });
                } else {
                    console.error('Error al cargar productos:', data.message);
                    productosGrid.innerHTML = '<div class="error-state">Error al cargar productos. Por favor, intenta nuevamente.</div>';
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                productosGrid.innerHTML = '<div class="error-state">Error al cargar productos. Por favor, intenta nuevamente.</div>';
            });
    }
    
    /**
     * Formatea números con separador de miles
     * @param {number} number - Número a formatear
     * @return {string} Número formateado
     */
    function formatNumber(number) {
        return new Intl.NumberFormat('es-CL').format(number);
    }
    
    // Configurar eventos para los botones de categoría
    categoriaButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Actualizar URL para reflejar la categoría seleccionada sin recargar la página
            if (category === 'todos') {
                history.pushState(null, '', `${baseUrl}/products`);
            } else {
                history.pushState(null, '', `${baseUrl}/products/category/${category}`);
            }
            
            loadProductsByCategory(category);
        });
    });
});