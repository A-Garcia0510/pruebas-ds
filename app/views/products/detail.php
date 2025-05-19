<?php
/**
 * Vista para detalle de producto
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';

// Preparar el nombre de la imagen
$nombre_imagen = strtolower(str_replace(' ', '_', $product->getName())) . '.jpg';
$imagen_ruta = "IMG-P/" . $nombre_imagen;
?>
<link rel="stylesheet" href="<?= AssetHelper::css('detalleproducto') ?>">
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
        
        <div class="descripcion">
            <h3>Descripción:</h3>
            <p><?= htmlspecialchars($product->getDescription()) ?></p>
        </div>
    </div>
</div>

<button id="carrito-btn" class="carrito-button" onclick="window.location.href='<?= AssetHelper::url('cart') ?>'">
    <img src="<?= AssetHelper::img('carro.png') ?>" alt="Carrito" class="carrito-logo">
    Ver Carrito
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const decrementarBtn = document.getElementById('decrementar');
    const incrementarBtn = document.getElementById('incrementar');
    const cantidadInput = document.getElementById('cantidad');
    const agregarCarritoBtn = document.getElementById('agregar-carrito');
    const productoId = agregarCarritoBtn.dataset.id;
    const maxStock = <?= $product->getStock() ?>;
    
    decrementarBtn.addEventListener('click', function() {
        let cantidad = parseInt(cantidadInput.value);
        if (cantidad > 1) {
            cantidadInput.value = cantidad - 1;
        }
    });
    
    incrementarBtn.addEventListener('click', function() {
        let cantidad = parseInt(cantidadInput.value);
        if (cantidad < maxStock) {
            cantidadInput.value = cantidad + 1;
        }
    });
    
    agregarCarritoBtn.addEventListener('click', function() {
        const cantidad = parseInt(cantidadInput.value);
        
        fetch('<?= AssetHelper::url('api/cart/add') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                producto_ID: productoId, 
                cantidad: cantidad 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto agregado al carrito con éxito.');
            } else {
                alert('Error al agregar producto: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el producto al carrito.');
        });
    });
});
</script>