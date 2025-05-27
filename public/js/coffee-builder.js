/**
 * Funcionalidad para el constructor de café personalizado
 */
document.addEventListener('DOMContentLoaded', function() {
    // Base URL para peticiones AJAX
    const baseUrl = window.location.origin + '/pruebas-ds/public';
    
    // Referencias a elementos DOM
    const formBuilder = document.getElementById('coffee-builder-form');
    const btnSaveRecipe = document.getElementById('btn-save-recipe');
    const btnPlaceOrder = document.getElementById('btn-place-order');
    const totalPrice = document.getElementById('total-price');
    
    // Estado del constructor
    let currentRecipe = {
        nombre: '',
        componentes: [],
        precio_total: 0
    };
    
    // Inicializar el constructor
    initializeBuilder();
    
    /**
     * Inicializa el constructor de café
     */
    function initializeBuilder() {
        // Cargar componentes disponibles
        loadComponents();
        
        // Configurar eventos
        if (formBuilder) {
            formBuilder.addEventListener('change', updateRecipe);
        }
        
        if (btnSaveRecipe) {
            btnSaveRecipe.addEventListener('click', saveRecipe);
        }
        
        if (btnPlaceOrder) {
            btnPlaceOrder.addEventListener('click', placeOrder);
        }
    }
    
    /**
     * Carga los componentes disponibles desde el servidor
     */
    function loadComponents() {
        fetch(`${baseUrl}/api/custom-coffee/get-components`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderComponents(data.componentes);
            } else {
                showError('Error al cargar componentes: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al cargar los componentes');
        });
    }
    
    /**
     * Renderiza los componentes en la interfaz
     * @param {Array} componentes Lista de componentes
     */
    function renderComponents(componentes) {
        const container = document.getElementById('componentes-container');
        if (!container) return;
        
        // Agrupar componentes por tipo
        const grupos = {
            base: [],
            leche: [],
            endulzante: [],
            topping: []
        };
        
        componentes.forEach(comp => {
            if (grupos[comp.tipo]) {
                grupos[comp.tipo].push(comp);
            }
        });
        
        // Renderizar cada grupo
        Object.entries(grupos).forEach(([tipo, items]) => {
            if (items.length === 0) return;
            
            const grupoDiv = document.createElement('div');
            grupoDiv.className = 'componente-grupo';
            grupoDiv.innerHTML = `
                <h3>${capitalizeFirst(tipo)}</h3>
                <div class="componentes-lista" data-tipo="${tipo}">
                    ${items.map(comp => `
                        <div class="componente-item">
                            <input type="radio" 
                                   name="componente_${tipo}" 
                                   id="comp_${comp.id}" 
                                   value="${comp.id}" 
                                   data-precio="${comp.precio}"
                                   data-nombre="${comp.nombre}">
                            <label for="comp_${comp.id}">
                                ${comp.nombre}
                                <span class="precio">$${formatNumber(comp.precio)}</span>
                            </label>
                        </div>
                    `).join('')}
                </div>
            `;
            
            container.appendChild(grupoDiv);
        });
    }
    
    /**
     * Actualiza la receta actual con los componentes seleccionados
     */
    function updateRecipe() {
        const formData = new FormData(formBuilder);
        const componentes = [];
        let precioTotal = 0;
        
        // Obtener nombre de la receta
        currentRecipe.nombre = formData.get('nombre_receta') || 'Mi Café Personalizado';
        
        // Obtener componentes seleccionados
        document.querySelectorAll('.componentes-lista').forEach(lista => {
            const tipo = lista.dataset.tipo;
            const selected = lista.querySelector('input[type="radio"]:checked');
            
            if (selected) {
                const precio = parseFloat(selected.dataset.precio);
                console.log(`Componente ${selected.dataset.nombre} - Precio: ${precio}`);
                
                const componente = {
                    id: selected.value,
                    tipo: tipo,
                    nombre: selected.dataset.nombre,
                    precio: precio
                };
                
                componentes.push(componente);
                precioTotal += precio;
            }
        });
        
        // Actualizar estado
        currentRecipe.componentes = componentes;
        currentRecipe.precio_total = parseFloat(precioTotal.toFixed(2));
        console.log('Precio total actualizado:', currentRecipe.precio_total);
        
        // Actualizar UI
        if (totalPrice) {
            totalPrice.textContent = `$${formatNumber(currentRecipe.precio_total)}`;
        }
        
        // Habilitar/deshabilitar botones según el estado
        updateButtonsState();
    }
    
    /**
     * Guarda la receta actual
     */
    function saveRecipe() {
        if (!validateRecipe()) return;
        
        fetch(`${baseUrl}/api/custom-coffee/save-recipe`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin', // Incluir cookies en la petición
            body: JSON.stringify(currentRecipe)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Receta guardada exitosamente');
                if (data.receta_id) {
                    currentRecipe.id = data.receta_id;
                    updateButtonsState();
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al guardar la receta', 'error');
        });
    }
    
    /**
     * Realiza el pedido de la receta actual
     */
    function placeOrder() {
        if (!validateRecipe()) return;
        
        // Asegurar que el precio sea un número decimal
        const precioTotal = parseFloat(currentRecipe.precio_total);
        console.log('Enviando precio total:', precioTotal);
        
        fetch(`${baseUrl}/api/custom-coffee/place-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                receta_id: currentRecipe.id,
                precio_total: precioTotal
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
                showNotification('Pedido realizado exitosamente');
                // Redirigir a la página de pedidos
                window.location.href = `${baseUrl}/custom-coffee/orders`;
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al realizar el pedido', 'error');
        });
    }
    
    /**
     * Valida que la receta actual sea válida
     * @returns {boolean}
     */
    function validateRecipe() {
        if (currentRecipe.componentes.length === 0) {
            showNotification('Debes seleccionar al menos un componente', 'error');
            return false;
        }
        
        if (!currentRecipe.nombre.trim()) {
            showNotification('Debes darle un nombre a tu receta', 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Actualiza el estado de los botones según la receta actual
     */
    function updateButtonsState() {
        if (btnSaveRecipe) {
            btnSaveRecipe.disabled = !validateRecipe();
        }
        
        if (btnPlaceOrder) {
            btnPlaceOrder.disabled = !currentRecipe.id || !validateRecipe();
        }
    }
    
    /**
     * Muestra una notificación temporal
     * @param {string} message Mensaje a mostrar
     * @param {string} type Tipo de notificación (success, error)
     */
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    /**
     * Muestra un mensaje de error
     * @param {string} message Mensaje de error
     */
    function showError(message) {
        const container = document.getElementById('componentes-container');
        if (container) {
            container.innerHTML = `<div class="error-message">${message}</div>`;
        }
    }
    
    /**
     * Formatea números con separador de miles
     * @param {number} number Número a formatear
     * @returns {string} Número formateado
     */
    function formatNumber(number) {
        return new Intl.NumberFormat('es-CL').format(number);
    }
    
    /**
     * Capitaliza la primera letra de una cadena
     * @param {string} str Cadena a capitalizar
     * @returns {string} Cadena capitalizada
     */
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}); 