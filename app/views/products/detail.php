<?php
/**
 * Vista para detalle de producto
 */

// Incluir encabezado y pie de página (temporalmente, si render no los carga)
require_once BASE_PATH . '/app/views/partials/header.php';

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';

// Preparar el nombre de la imagen
$nombre_imagen = strtolower(str_replace(' ', '_', $product->getName())) . '.jpg';
$imagen_ruta = "IMG-P/" . $nombre_imagen;
?>
<link rel="stylesheet" href="<?= AssetHelper::css('detalleproducto') ?>">
<link rel="stylesheet" href="<?= AssetHelper::css('reviews') ?>">
<div class="page-title">
    <h1>Detalle del Producto</h1>
    <a href="<?= AssetHelper::url('products') ?>" class="volver-link">
        <i>←</i> Volver a la tienda
    </a>
</div>

<div class="producto-container">
    <div class="producto-imagen">
        <?php
        // Intentar cargar la imagen del producto, si existe
        if (file_exists(BASE_PATH . '/public/' . $imagen_ruta)) {
            echo '<img src="' . AssetHelper::url($imagen_ruta) . '" alt="' . htmlspecialchars($product->getName()) . '">';
        } else {
            // Si no existe, usar un placeholder
            echo '<img src="/api/placeholder/500/500" alt="' . htmlspecialchars($product->getName()) . '">';
        }
        ?>
    </div>
    
    <div class="producto-info">
        <h2><?= htmlspecialchars($product->getName()) ?></h2>
        <span class="categoria"><?= htmlspecialchars($product->getCategory()) ?></span>
        <p class="precio">$<?= number_format($product->getPrice(), 0, ',', '.') ?></p>
        <p class="stock">Disponibilidad: <?= $product->getStock() ?> unidades</p>
        
        <?php if ($isLoggedIn): ?>
            <div class="cantidad-selector">
                <label for="cantidad">Cantidad:</label>
                <div class="cantidad-controles">
                    <button type="button" id="decrementar">-</button>
                    <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?= $product->getStock() ?>">
                    <button type="button" id="incrementar">+</button>
                </div>
            </div>
            
            <button class="agregar-btn" id="agregar-carrito" data-id="<?= $product->getId() ?>">
                Agregar al Carrito
            </button>
        <?php else: ?>
            <div class="login-required">
                <p>Para agregar productos al carrito, debes <a href="<?= AssetHelper::url('login') ?>">iniciar sesión</a></p>
            </div>
        <?php endif; ?>
        
        <div class="descripcion">
            <h3>Descripción:</h3>
            <p><?= htmlspecialchars($product->getDescription()) ?></p>
        </div>
    </div>
</div>

<div id="reviews-section" class="product-reviews-section">
    <h2>Reseñas de Clientes</h2>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="add-review-form">
            <h3>Deja tu Reseña</h3>
            <form id="add-review-form">
                <input type="hidden" name="producto_ID" value="<?= $product->getId() ?>">
                <div class="form-group">
                    <label for="rating">Calificación:</label>
                    <select id="rating" name="calificacion" required>
                        <option value="">Selecciona una calificación</option>
                        <option value="5">5 Estrellas</option>
                        <option value="4">4 Estrellas</option>
                        <option value="3">3 Estrellas</option>
                        <option value="2">2 Estrellas</option>
                        <option value="1">1 Estrella</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="review-content">Tu Comentario:</label>
                    <textarea id="review-content" name="contenido" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Reseña</button>
            </form>
        </div>
    <?php else: ?>
        <p>Inicia sesión para dejar una reseña.</p>
    <?php endif; ?>

    <div id="reviews-list">
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item" data-review-id="<?= $review['review_ID'] ?>">
                    <div class="review-header">
                        <span class="reviewer-name"><?= htmlspecialchars($review['nombre'] . ' ' . $review['apellidos']) ?></span>
                        <span class="review-date"><?= date('d/m/Y H:i', strtotime($review['fecha_creacion'])) ?></span>
                         <div class="review-actions">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['usuario_ID']): ?>
                                <button class="delete-review-btn" data-review-id="<?= $review['review_ID'] ?>">Eliminar</button>
                            <?php endif; ?>
                            <button class="report-review-btn" data-review-id="<?= $review['review_ID'] ?>" <?= (isset($review['reportado_por_usuario']) && $review['reportado_por_usuario']) ? 'disabled' : '' ?>>
                                <?= (isset($review['reportado_por_usuario']) && $review['reportado_por_usuario']) ? 'Reportado' : 'Reportar' ?>
                            </button>
                        </div>
                    </div>
                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $review['calificacion'] ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <div class="review-content">
                        <p><?= nl2br(htmlspecialchars($review['contenido'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aún no hay reseñas para este producto. ¡Sé el primero en dejar una!</p>
        <?php endif; ?>
    </div>

    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Reportar Reseña</h2>
            <form id="report-review-form">
                <input type="hidden" id="report-review-id" name="review_ID">
                <div class="form-group">
                    <label for="report-reason">Razón del Reporte:</label>
                    <textarea id="report-reason" name="razon" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Enviar Reporte</button>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Obtener la URL base para las peticiones API
    // Usa window.location.origin y pathname para construir la URL base dinámicamente
    // Esto maneja casos donde la app no está en la raíz del dominio
    const baseUrl = window.location.origin + window.location.pathname.split('/productos')[0].split('/products')[0];
    const addReviewUrl = `${baseUrl}/api/products/review/add`;
    const reportReviewUrl = `${baseUrl}/api/products/review/report`;
    const deleteReviewUrl = `${baseUrl}/api/products/review/delete`; // Nueva URL para eliminar

    console.log('API Base URL:', baseUrl);

    // ------------------------------------------------------------------------
    // Manejo del formulario para añadir reseña
    // ------------------------------------------------------------------------
    const addReviewForm = document.getElementById('add-review-form');
    if (addReviewForm) {
        addReviewForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            console.log('Datos de la nueva reseña:', data);

            try {
                const response = await fetch(addReviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                });

                 const result = await response.json();
                 console.log('Respuesta al añadir reseña:', result);

                if (result.success) {
                    // Opcional: Actualizar la lista de reseñas dinámicamente sin recargar
                    // Aquí podrías crear el HTML para la nueva reseña y añadirlo a #reviews-list
                    alert('Reseña añadida exitosamente. Puede que necesite moderación antes de aparecer.');
                    this.reset(); // Limpiar el formulario
                     // Deshabilitar temporalmente el formulario para evitar múltiples envíos rápidos
                     // Opcional, dependiendo de la UX deseada

                } else {
                    alert(result.message || 'Error al añadir la reseña.');
                }
            } catch (error) {
                console.error('Error en la petición de añadir reseña:', error);
                alert('Ocurrió un error al enviar tu reseña.');
            }
        });
    }

    // ------------------------------------------------------------------------
    // Manejo del Modal de Reporte
    // ------------------------------------------------------------------------
    const reportModal = document.getElementById('reportModal');
    const closeButton = reportModal.querySelector('.close-button');
    const reportReviewForm = document.getElementById('report-review-form');
    const reportReviewIdInput = document.getElementById('report-review-id');
    let currentReviewToReportId = null;

    // Abrir modal al hacer clic en Reportar
    document.querySelectorAll('.report-review-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentReviewToReportId = this.dataset.reviewId;
            reportReviewIdInput.value = currentReviewToReportId;
            reportModal.classList.add('show');
        });
    });

    // Cerrar modal
    closeButton.addEventListener('click', function() {
        reportModal.classList.remove('show');
        reportReviewForm.reset();
    });

    // Cerrar modal si se clica fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target == reportModal) {
            reportModal.classList.remove('show');
             reportReviewForm.reset();
        }
    });

    // Enviar formulario de reporte
     if (reportReviewForm) {
        reportReviewForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            console.log('Datos del reporte:', data);

            try {
                 const response = await fetch(reportReviewUrl, {
                    method: 'POST',
                    headers: {
                         'Content-Type': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest'
                     },
                     body: JSON.stringify(data)
                 });

                 const result = await response.json();
                 console.log('Respuesta al reportar reseña:', result);

                 alert(result.message); // Mostrar mensaje de éxito o error

                 if (result.success) {
                    reportModal.classList.remove('show');
                     reportReviewForm.reset();
                     // Opcional: Deshabilitar el botón de reportar para esta reseña
                     const reportButton = document.querySelector(`.report-review-btn[data-review-id="${data.review_ID}"]`);
                     if(reportButton) {
                        reportButton.textContent = 'Reportado';
                        reportButton.disabled = true;
                     }
                 }

            } catch (error) {
                console.error('Error en la petición de reporte:', error);
                alert('Ocurrió un error al enviar el reporte.');
            }
        });
    }

    // ------------------------------------------------------------------------
    // Manejo de la eliminación de reseña (Nuevo)
    // ------------------------------------------------------------------------
    // Usamos delegación de eventos ya que las reseñas pueden cargarse dinámicamente
    document.getElementById('reviews-list').addEventListener('click', async function(event) {
        // Verificar si el clic fue en un botón de eliminar reseña
        if (event.target && event.target.classList.contains('delete-review-btn')) {
            const deleteButton = event.target;
            const reviewId = deleteButton.dataset.reviewId;
            console.log('Clic en botón Eliminar para reseña:', reviewId);

            // Confirmar antes de eliminar
            if (confirm('¿Estás seguro de que quieres eliminar esta reseña?')) {
                try {
                    const response = await fetch(deleteReviewUrl, {
                        method: 'POST',
                         headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                         body: JSON.stringify({ review_id: reviewId })
                    });

                    const result = await response.json();
                    console.log('Respuesta al eliminar reseña:', result);

                    alert(result.message); // Mostrar mensaje del servidor

                    if (result.success) {
                        // Eliminar la reseña del DOM
                        const reviewItem = deleteButton.closest('.review-item');
                        if (reviewItem) {
                            reviewItem.remove();
                        }
                         // Opcional: Mostrar mensaje de éxito en la UI
                    } else {
                        // Mostrar mensaje de error si la eliminación no fue exitosa
                         console.error('Error al eliminar reseña:', result.message);
                    }

                } catch (error) {
                    console.error('Error en la petición de eliminar reseña:', error);
                    alert('Ocurrió un error al intentar eliminar la reseña.');
                }
            }
        }
    });

}); // Fin DOMContentLoaded
</script>

<?php
// Incluir pie de página (temporalmente)
require_once BASE_PATH . '/app/views/partials/footer.php';
?>