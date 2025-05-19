<?php
/**
 * Vista para listar productos
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>

<div class="page-title">
    <h1>Nuestros Productos</h1>
    <p>Descubre nuestras deliciosas especialidades y preparaciones</p>
</div>

<div class="categoria-bar">
    <button id="btn-todos" class="<?= !isset($currentCategory) ? 'active' : '' ?>" 
            data-category="todos">Todos</button>
    <?php foreach ($categories as $category): ?>
        <button id="btn-<?= strtolower(str_replace(' ', '-', $category)) ?>" 
                class="<?= isset($currentCategory) && $currentCategory === $category ? 'active' : '' ?>"
                data-category="<?= $category ?>"><?= $category ?></button>
    <?php endforeach; ?>
</div>

<div id="productos" class="productos-grid">
    <?php if (empty($products)): ?>
        <div class="empty-state">No se encontraron productos en esta categoría.</div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="producto-tarjeta" data-id="<?= $product->getId() ?>">
                <div class="producto-imagen">
                    <?php
                    // Intentar cargar la imagen del producto, si existe
                    $imagen_nombre = strtolower(str_replace(' ', '_', $product->getName())) . '.jpg';
                    $imagen_ruta = "IMG-P/" . $imagen_nombre;
                    if (file_exists(BASE_PATH . '/public/' . $imagen_ruta)) {
                        echo '<img src="' . AssetHelper::url($imagen_ruta) . '" alt="' . htmlspecialchars($product->getName()) . '">';
                    } else {
                        // Si no existe, usar un placeholder
                        echo '<img src="/api/placeholder/300/300" alt="' . htmlspecialchars($product->getName()) . '">';
                    }
                    ?>
                </div>
                <div class="producto-info">
                    <h2><?= htmlspecialchars($product->getName()) ?></h2>
                    <p class="categoria"><?= htmlspecialchars($product->getCategory()) ?></p>
                    <p class="precio">$<?= number_format($product->getPrice(), 0, ',', '.') ?></p>
                    <p class="stock">Disponible: <?= $product->getStock() ?> unidades</p>
                    <div class="acciones">
                        <a href="<?= AssetHelper::url('products/detail/' . $product->getId()) ?>" class="ver-detalle">Ver Detalle</a>
                        <button class="agregar" data-id="<?= $product->getId() ?>">Agregar</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<button id="carrito-btn" class="carrito-button" onclick="window.location.href='<?= AssetHelper::url('cart') ?>'">
    <img src="<?= AssetHelper::img('carro.png') ?>" alt="Carrito" class="carrito-logo">
    Ver Carrito
</button>

<script src="<?= AssetHelper::js('products') ?>"></script>