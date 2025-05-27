<?php
/**
 * Vista de moderación de reseñas
 */

// Asegurar que los helpers estén disponibles si no lo están ya
if (!function_exists('AssetHelper')) {
    require_once BASE_PATH . '/app/helpers/AssetHelper.php';
}

?>

<div class="moderation-container">
    <div class="moderation-header">
        <h1>Moderación de Reseñas</h1>
    </div>

    <div class="moderation-tabs">
        <button class="moderation-tab active" data-target="pending">Pendientes</button>
        <button class="moderation-tab" data-target="reported">Reportadas</button>
    </div>

    <div class="tab-content">
        <div id="pending" class="tab-pane active">
            <h2>Reseñas Pendientes</h2>
            <?php if (empty($pendingReviews)): ?>
                <p class="no-reviews">No hay reseñas pendientes de moderación.</p>
            <?php else: ?>
                <?php foreach ($pendingReviews as $review): ?>
                    <div class="review-card" data-review-id="<?= $review['review_ID'] ?>">
                        <div class="review-header">
                            <div class="product-info">
                                <h3 class="review-product"><?= htmlspecialchars($review['nombre_producto']) ?></h3>
                            </div>
                            <div class="reviewer-info">
                                <span class="review-author"><?= htmlspecialchars($review['nombre'] . ' ' . $review['apellidos']) ?></span>
                                <span class="review-date"><?= date('d/m/Y H:i', strtotime($review['fecha_creacion'])) ?></span>
                            </div>
                        </div>
                        <div class="review-content">
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= $review['calificacion'] ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <p><?= nl2br(htmlspecialchars($review['contenido'])) ?></p>
                        </div>
                        <div class="review-actions">
                            <button class="approve-btn" data-action="aprobada">Aprobar</button>
                            <button class="reject-btn" data-action="rechazada">Rechazar</button>
                            <div class="review-comment">
                                <textarea class="moderation-comment" placeholder="Comentario (opcional)"></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="reported" class="tab-pane">
            <h2>Reseñas Reportadas</h2>
            <?php if (empty($reportedReviews)): ?>
                <p class="no-reviews">No hay reseñas reportadas.</p>
            <?php else: ?>
                <?php foreach ($reportedReviews as $review): ?>
                     <div class="review-card" data-review-id="<?= $review['review_ID'] ?>">
                        <div class="review-header">
                            <div class="product-info">
                                <h3 class="review-product"><?= htmlspecialchars($review['nombre_producto']) ?></h3>
                                <span class="reports-count"><?= $review['reportes_count'] ?> reporte<?= $review['reportes_count'] > 1 ? 's' : '' ?></span>
                            </div>
                            <div class="reviewer-info">
                                <span class="review-author"><?= htmlspecialchars($review['nombre'] . ' ' . $review['apellidos']) ?></span>
                                <span class="review-date"><?= date('d/m/Y H:i', strtotime($review['fecha_creacion'])) ?></span>
                            </div>
                        </div>
                        <div class="review-content">
                             <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= $review['calificacion'] ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <p><?= nl2br(htmlspecialchars($review['contenido'])) ?></p>
                        </div>
                         <div class="review-actions">
                            <button class="approve-btn" data-action="aprobada">Aprobar</button>
                            <button class="reject-btn" data-action="rechazada">Rechazar</button>
                             <div class="review-comment">
                                <textarea class="moderation-comment" placeholder="Comentario (opcional)"></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// No incluir el CSS y JS directamente aquí si se cargan en el layout principal
// Si tu layout principal no carga automáticamente 'moderation.css' y 'moderation.js',
// puedes descomentar las siguientes líneas. Preferiblemente, cárgalos en el layout.

/*
$css = ['moderation'];
$js = ['moderation'];
*/
?>

<style>
/* Estilos mejorados para la sección de Moderación */
.moderation-container {
    max-width: 1000px; /* Ajusta el ancho máximo */
    margin: 2rem auto; /* Centrar el contenedor */
    padding: 1.5rem; /* Padding interno */
    background-color: #f8f9fa; /* Fondo suave */
    border-radius: 8px; /* Bordes redondeados */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Sombra sutil */
}

.moderation-header h1 {
    color: #343a40; /* Color oscuro para el título */
    font-size: 2.2rem; /* Tamaño de fuente */
    margin-bottom: 1.5rem; /* Espacio debajo del título */
    text-align: center; /* Centrar título */
    border-bottom: 2px solid #e9ecef; /* Línea separadora */
    padding-bottom: 1rem; /* Espacio entre título y línea */
}

.moderation-tabs {
    display: flex; /* Alinear pestañas en fila */
    justify-content: center; /* Centrar pestañas */
    gap: 1.5rem; /* Espacio entre pestañas */
    margin-bottom: 2rem; /* Espacio debajo de las pestañas */
    border-bottom: none; /* Eliminar borde inferior duplicado */
}

.moderation-tab {
    padding: 0.75rem 1.5rem; /* Padding en botones de pestaña */
    border: 1px solid #007bff; /* Borde para el botón */
    border-radius: 5px; /* Bordes redondeados */
    background-color: #ffffff; /* Fondo blanco */
    color: #007bff; /* Color de texto */
    font-size: 1.1rem; /* Tamaño de fuente */
    cursor: pointer; /* Cursor de mano */
    transition: all 0.3s ease; /* Transición suave */
}

.moderation-tab:hover {
    background-color: #e9ecef; /* Fondo al pasar el ratón */
}

.moderation-tab.active {
    background-color: #007bff; /* Fondo para pestaña activa */
    color: white; /* Texto blanco para pestaña activa */
    border-color: #007bff; /* Borde para pestaña activa */
    font-weight: bold; /* Texto negrita */
}

.tab-content {
    /* Puedes mantener display: flex o cambiar a block si prefieres */
    /* display: flex; */ 
    /* gap: 2rem; */ /* Ajusta o remueve gap según necesites los paneles uno al lado del otro */
}

.tab-pane {
    flex: 1; /* Permite que el panel ocupe el espacio disponible */
    display: none; /* Ocultar paneles inactivos */
    background-color: #ffffff; /* Fondo blanco para el contenido del panel */
    padding: 1.5rem; /* Padding interno del panel */
    border-radius: 8px; /* Bordes redondeados */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); /* Sombra para el panel */
}

.tab-pane.active {
    display: block; /* Mostrar panel activo */
}

.tab-pane h2 {
    color: #495057; /* Color para subtítulos */
    font-size: 1.8rem; /* Tamaño de fuente */
    margin-bottom: 1.5rem; /* Espacio debajo del subtítulo */
    border-bottom: 1px dashed #ced4da; /* Línea punteada */
    padding-bottom: 0.8rem; /* Espacio */
}

/* Estilos para las tarjetas de reseñas */
.review-card {
    border: 1px solid #dee2e6; /* Borde más suave */
    border-radius: 5px; /* Bordes redondeados */
    padding: 1.2rem; /* Padding */
    margin-bottom: 1.5rem; /* Espacio entre tarjetas */
    background-color: #ffffff; /* Fondo blanco */
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04); /* Sombra ligera */
}

.review-header {
    display: flex; /* Alinear elementos en fila */
    justify-content: space-between; /* Espacio entre info de producto y revisor */
    align-items: flex-start; /* Alinear arriba */
    margin-bottom: 1rem; /* Espacio debajo del header */
    flex-wrap: wrap; /* Permitir que los elementos se envuelvan */
}

.product-info h3.review-product {
    margin: 0; /* Sin margen superior/inferior */
    color: #0056b3; /* Color azul para nombre de producto */
    font-size: 1.3rem; /* Tamaño de fuente */
    margin-bottom: 0.5rem; /* Espacio */
}

.reports-count {
    background: #dc3545; /* Fondo rojo para reportes */
    color: white; /* Texto blanco */
    padding: 0.3rem 0.6rem; /* Padding */
    border-radius: 4px; /* Bordes redondeados */
    font-size: 0.9rem; /* Tamaño de fuente */
    margin-left: 1rem; /* Espacio a la izquierda */
    font-weight: bold; /* Negrita */
}

.reviewer-info {
    text-align: right; /* Alinear texto a la derecha */
}

span.review-author {
    display: block; /* Cada uno en su línea */
    font-weight: bold; /* Negrita */
    color: #5a6268; /* Color gris oscuro */
}

span.review-date {
    color: #6c757d; /* Color gris */
    font-size: 0.9rem; /* Tamaño */
}

.review-content {
    margin: 1rem 0; /* Espacio */
    line-height: 1.6; /* Interlineado */
    color: #343a40; /* Color de texto */
}

.rating .star {
    font-size: 1.3rem; /* Tamaño de estrella */
}

.review-actions {
    margin-top: 1.5rem; /* Espacio arriba */
    display: flex; /* Alinear elementos en fila */
    gap: 0.8rem; /* Espacio entre elementos */
    align-items: center; /* Centrar verticalmente */
    flex-wrap: wrap; /* Permitir que los elementos se envuelvan */
}

.review-actions button {
    padding: 0.6rem 1.2rem; /* Padding en botones */
    border: none; /* Sin borde */
    border-radius: 4px; /* Bordes redondeados */
    cursor: pointer; /* Cursor de mano */
    transition: background-color 0.3s ease; /* Transición */
    font-weight: 600; /* Negrita */
}

.approve-btn {
    background-color: #28a745; /* Fondo verde */
    color: white; /* Texto blanco */
}

.approve-btn:hover {
    background-color: #218838; /* Fondo verde oscuro al pasar el ratón */
}

.reject-btn {
    background-color: #dc3545; /* Fondo rojo */
    color: white; /* Texto blanco */
}

.reject-btn:hover {
    background-color: #c82333; /* Fondo rojo oscuro al pasar el ratón */
}

.review-comment {
    flex-grow: 1; /* Hacer que el comentario ocupe el espacio restante */
    min-width: 200px; /* Ancho mínimo para el textarea */
}

.review-comment textarea {
    width: 100%; /* Ocupar el ancho de su contenedor */
    min-height: 80px; /* Altura mínima */
    padding: 0.6rem; /* Padding */
    border: 1px solid #ced4da; /* Borde */
    border-radius: 4px; /* Bordes redondeados */
    resize: vertical; /* Permitir redimensionar verticalmente */
    font-size: 1rem; /* Tamaño de fuente */
}

.no-reviews {
    text-align: center; /* Centrar texto */
    color: #6c757d; /* Color gris */
    padding: 2rem; /* Padding */
    background-color: #e9ecef; /* Fondo suave */
    border-radius: 5px; /* Bordes redondeados */
    font-size: 1.2rem; /* Tamaño de fuente */
}

/* Estilos adicionales para mejorar la consistencia visual */
body { background-color: #f4f7f6; } /* Un fondo ligero para toda la página */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener la URL base del documento actual
    const baseUrl = window.location.origin + window.location.pathname.split('/dashboard')[0];
    const apiUrl = `${baseUrl}/api/dashboard/moderate-review`;

    console.log('URL base construida:', baseUrl);
    console.log('URL completa para fetch:', apiUrl);

    // Función para manejar la moderación de reseñas
    async function handleModeration(reviewId, action, comment = '', event) {
        console.log('handleModeration activado por:', event.target);
        const data = {
            review_id: reviewId,
            action: action,
            comment: comment
        };

        console.log('Datos enviados:', data);

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            console.log('Respuesta recibida (fetch):', response);
            console.log('Status (fetch):', response.status);
            console.log('Headers (fetch):', Object.fromEntries(response.headers.entries()));

            if (!response.ok) {
                // Intenta leer el cuerpo de la respuesta incluso si no es JSON para depuración
                 const errorBody = await response.text();
                 console.error('HTTP error body:', errorBody);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no es JSON:', text);
                throw new Error('La respuesta del servidor no es JSON');
            }

            const result = await response.json();
            console.log('Resultado (JSON):', result);

            if (result.success) {
                // Actualizar la interfaz
                const reviewElement = document.querySelector(`[data-review-id="${reviewId}"]`);
                if (reviewElement) {
                    reviewElement.remove();
                }
                // Opcional: Mostrar mensaje de éxito más amigable
                console.log('Éxito:', result.message);
                // alert('Reseña moderada exitosamente'); // Evitamos alert para no interrumpir
            } else {
                throw new Error(result.message || 'Error desconocido al moderar la reseña');
            }
        } catch (error) {
            console.error('Error en handleModeration:', error);
            // alert('Error al moderar la reseña: ' + error.message); // Evitamos alert
        }
    }

    // Agregar event listeners a los botones con un identificador
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.dataset.listener = 'moderation-script'; // Identificador
        btn.addEventListener('click', function(event) {
            console.log('Clic en botón Aprobar (listener: moderation-script)');
            const reviewId = this.closest('.review-card').dataset.reviewId;
            handleModeration(reviewId, 'aprobada', '', event);
        });
    });

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.dataset.listener = 'moderation-script'; // Identificador
        btn.addEventListener('click', function(event) {
             console.log('Clic en botón Rechazar (listener: moderation-script)');
            const reviewId = this.closest('.review-card').dataset.reviewId;
            // Obtener comentario del textarea asociado si existe
             const commentTextarea = this.closest('.review-card').querySelector('.moderation-comment');
            const comment = commentTextarea ? commentTextarea.value : '';

            if (comment !== null) { // prompt() devuelve null si se cancela
                 handleModeration(reviewId, 'rechazada', comment, event);
            }
        });
    });

    // Manejar cambio de pestañas (asegurando que no haya duplicados si se carga más de una vez)
    const tabButtons = document.querySelectorAll('.moderation-tab');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    // Remover listeners existentes para evitar duplicados
     tabButtons.forEach(button => {
        // Clona y reemplaza para remover listeners (método simple)
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
    });

    // Seleccionar los botones *después* de haberlos potencialmente reemplazado
    const updatedTabButtons = document.querySelectorAll('.moderation-tab');

    updatedTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Cambio de pestaña:', this.dataset.target);
            const tabId = this.dataset.target;

            // Actualizar botones
            updatedTabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Actualizar paneles
            tabPanes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Asegurarse de que al menos una pestaña esté activa al cargar
     const activeTabButton = document.querySelector('.moderation-tab.active');
     if (activeTabButton) {
        const tabId = activeTabButton.dataset.target;
         document.getElementById(tabId).classList.add('active');
     } else if (updatedTabButtons.length > 0) {
         // Si no hay ninguna activa, activar la primera
        updatedTabButtons[0].classList.add('active');
        document.getElementById(updatedTabButtons[0].dataset.target).classList.add('active');
     }
});
</script> 