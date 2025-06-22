<?php
/**
 * Vista del historial de transacciones mejorada
 */
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
$user_id = $_SESSION['user_id'] ?? null;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= AssetHelper::css('loyalty') ?>">

<div class="loyalty-container">
    
    <!-- Header unificado -->
    <div class="loyalty-rewards-header">
        <div class="rewards-header-content">
            <h1><i class="fas fa-history"></i> Historial de Transacciones</h1>
            <p>Sigue el rastro de todos los puntos que has ganado y canjeado.</p>
        </div>
    </div>
    
    <!-- Tarjetas de Resumen -->
    <div class="transactions-summary-grid">
        <div class="summary-card earned">
            <div class="summary-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="summary-info">
                <span class="summary-label">Puntos Ganados</span>
                <span class="summary-value" id="total-earned">0</span>
            </div>
        </div>
        <div class="summary-card redeemed">
            <div class="summary-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="summary-info">
                <span class="summary-label">Puntos Canjeados</span>
                <span class="summary-value" id="total-redeemed">0</span>
            </div>
        </div>
        <div class="summary-card balance">
            <div class="summary-icon"><i class="fas fa-wallet"></i></div>
            <div class="summary-info">
                <span class="summary-label">Balance Actual</span>
                <span class="summary-value" id="current-balance">0</span>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-container">
        <div class="filter-group search-group">
            <label for="search-transactions"><i class="fas fa-search"></i> Buscar por descripción</label>
            <div class="filter-control">
                <input type="text" id="search-transactions" placeholder="Escribe para buscar...">
            </div>
        </div>
        <div class="filter-group">
            <label for="type-filter">Tipo</label>
            <div class="filter-control">
                <select id="type-filter">
                    <option value="">Todos</option>
                    <option value="earn">Ganados</option>
                    <option value="redeem">Canjeados</option>
                    <option value="bonus">Bonos</option>
                </select>
            </div>
        </div>
        <div class="filter-group">
            <label for="date-filter">Período</label>
            <div class="filter-control">
                <select id="date-filter">
                    <option value="">Siempre</option>
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
            </div>
        </div>
        <div class="filter-group">
            <label for="sort-filter">Ordenar</label>
            <div class="filter-control">
                <select id="sort-filter">
                    <option value="date-desc">Recientes</option>
                    <option value="date-asc">Antiguos</option>
                    <option value="points-desc">Mayor Puntaje</option>
                    <option value="points-asc">Menor Puntaje</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Contenedor de la lista de transacciones -->
    <div class="transactions-list-container">
        <div id="transactions-list">
            <!-- Las transacciones se renderizan aquí -->
        </div>
    </div>
    
    <!-- Estados -->
    <div id="loading-state" class="loading-state" style="display: none;">Cargando...</div>
    <div id="empty-state" class="empty-state" style="display: none;">No se encontraron transacciones.</div>
    <div id="error-state" class="error-state" style="display: none;">Error al cargar.</div>
    <div id="no-more-transactions" class="no-more-transactions" style="display: none;">Fin de los resultados.</div>

</div>

<template id="transaction-item-template">
    <div class="transaction-item">
        <div class="transaction-icon-col">
            <div class="transaction-icon">
                <i class="fas fa-circle"></i>
            </div>
        </div>
        <div class="transaction-details-col">
            <p class="description">Descripción de la transacción</p>
            <p class="date">Fecha y hora</p>
        </div>
        <div class="transaction-points-col">
            <span class="points">0</span>
        </div>
    </div>
</template>


<script>
document.addEventListener('DOMContentLoaded', () => {
    // Asegurarse de que el API y el ID de usuario están disponibles
    if (typeof LoyaltyAPI === 'undefined' || typeof window.currentUserId === 'undefined') {
        document.getElementById('error-state').style.display = 'block';
        document.getElementById('error-state').textContent = 'Error crítico: La aplicación no está cargada correctamente.';
        return;
    }
    const api = window.loyaltyAPI;
    const userId = window.currentUserId;

    // Elementos del DOM
    const listContainer = document.getElementById('transactions-list');
    const template = document.getElementById('transaction-item-template');
    
    // Estados
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const errorState = document.getElementById('error-state');
    const noMoreMsg = document.getElementById('no-more-transactions');

    // Filtros
    const searchInput = document.getElementById('search-transactions');
    const typeFilter = document.getElementById('type-filter');
    const dateFilter = document.getElementById('date-filter');
    const sortFilter = document.getElementById('sort-filter');

    let allTransactions = [];
    let page = 1;
    let isLoading = false;

    // --- Funciones de Renderizado ---
    function renderTransactions(transactions) {
        if (transactions.length === 0) {
            emptyState.style.display = 'block';
            return;
        }
        emptyState.style.display = 'none';

        const groupedByDate = transactions.reduce((acc, tx) => {
            const date = new Date(tx.created_at).toLocaleDateString(undefined, {
                year: 'numeric', month: 'long', day: 'numeric'
            });
            if (!acc[date]) {
                acc[date] = [];
            }
            acc[date].push(tx);
            return acc;
        }, {});

        const fragment = document.createDocumentFragment();
        for (const date in groupedByDate) {
            const groupContainer = document.createElement('div');
            groupContainer.className = 'transaction-group';
            
            const dateHeader = document.createElement('h3');
            dateHeader.className = 'transaction-group-date';
            dateHeader.textContent = date;
            groupContainer.appendChild(dateHeader);

            groupedByDate[date].forEach(tx => {
                const clone = template.content.cloneNode(true);
                const item = clone.querySelector('.transaction-item');
                const iconContainer = clone.querySelector('.transaction-icon');
                const icon = iconContainer.querySelector('i');
                
                const typeInfo = getTransactionTypeInfo(tx.transaction_type);
                item.classList.add(typeInfo.typeClass);
                icon.className = `fas ${typeInfo.icon}`;
                
                clone.querySelector('.description').textContent = tx.description || 'Transacción';
                clone.querySelector('.date').textContent = new Date(tx.created_at).toLocaleTimeString();
                
                const pointsEl = clone.querySelector('.points');
                const points = tx.points_amount;
                pointsEl.textContent = `${points >= 0 ? '+' : ''}${points.toLocaleString()}`;

                groupContainer.appendChild(clone);
            });
            fragment.appendChild(groupContainer);
        }
        listContainer.appendChild(fragment);
    }
    
    function getTransactionTypeInfo(type) {
        const map = {
            'earn': { icon: 'fa-plus', typeClass: 'earn' },
            'redeem': { icon: 'fa-gift', typeClass: 'redeem' },
            'bonus': { icon: 'fa-star', typeClass: 'bonus' },
            'referral': { icon: 'fa-users', typeClass: 'referral' },
            'adjustment': { icon: 'fa-sliders-h', typeClass: 'adjustment' },
            'expiry': { icon: 'fa-clock', typeClass: 'expiry' }
        };
        return map[type] || { icon: 'fa-circle', typeClass: 'default' };
    }

    // --- Lógica de Carga y Filtro ---
    function applyFiltersAndSort() {
        let processed = [...allTransactions];
        const searchTerm = searchInput.value.toLowerCase();
        const type = typeFilter.value;
        const days = parseInt(dateFilter.value, 10);
        const [sortBy, sortDir] = sortFilter.value.split('-');

        // Búsqueda
        if (searchTerm) {
            processed = processed.filter(tx => tx.description.toLowerCase().includes(searchTerm));
        }
        // Tipo
        if (type) {
            processed = processed.filter(tx => tx.transaction_type === type);
        }
        // Fecha
        if (days) {
            const cutoff = new Date();
            cutoff.setDate(cutoff.getDate() - days);
            processed = processed.filter(tx => new Date(tx.created_at) > cutoff);
        }
        // Ordenamiento
        processed.sort((a, b) => {
            let valA, valB;
            if (sortBy === 'date') {
                valA = new Date(a.created_at);
                valB = new Date(b.created_at);
            } else { // points
                valA = a.points_amount;
                valB = b.points_amount;
            }
            return sortDir === 'asc' ? valA - valB : valB - valA;
        });

        listContainer.innerHTML = '';
        renderTransactions(processed);
        updateSummary(processed);
    }

    function loadInitialData() {
        isLoading = true;
        loadingState.style.display = 'block';
        listContainer.style.display = 'none';

        Promise.all([
            api.getUserTransactions(userId, 1, 1000), // Cargar un lote grande para filtrar en cliente
            api.getUserProfile(userId)
        ]).then(([transactionsResponse, profileResponse]) => {
            if (transactionsResponse && transactionsResponse.success) {
                allTransactions = transactionsResponse.data;
                listContainer.style.display = 'block';
                applyFiltersAndSort();
            } else {
                throw new Error("Error cargando transacciones");
            }
            if (profileResponse && profileResponse.success) {
                 document.getElementById('current-balance').textContent = profileResponse.data.current_points.toLocaleString();
            }
        }).catch(err => {
            console.error(err);
            errorState.style.display = 'block';
        }).finally(() => {
            isLoading = false;
            loadingState.style.display = 'none';
        });
    }
    
    function updateSummary(transactions) {
        const totalEarned = transactions.filter(t => t.points_amount > 0).reduce((sum, t) => sum + t.points_amount, 0);
        const totalRedeemed = transactions.filter(t => t.points_amount < 0).reduce((sum, t) => sum + t.points_amount, 0);
        
        document.getElementById('total-earned').textContent = totalEarned.toLocaleString();
        document.getElementById('total-redeemed').textContent = Math.abs(totalRedeemed).toLocaleString();
    }

    // --- Event Listeners ---
    [searchInput, typeFilter, dateFilter, sortFilter].forEach(el => {
        el.addEventListener('change', applyFiltersAndSort);
    });
     searchInput.addEventListener('input', applyFiltersAndSort);

    // Carga inicial
    loadInitialData();
});
</script>
