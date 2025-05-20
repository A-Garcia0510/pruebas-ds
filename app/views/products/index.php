<?php
/**
 * Vista para listar productos
 */

// Asegurarnos de que las clases helper estén disponibles
require_once BASE_PATH . '/app/helpers/AssetHelper.php';
require_once BASE_PATH . '/app/helpers/ViewHelper.php';
?>
<link rel="stylesheet" href="<?= AssetHelper::css('productos') ?>">

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

<!-- Cambiar la referencia del script a main.js en lugar de products.js -->
<script src="<?= AssetHelper::js('main') ?>"></script>
<script src="<?= AssetHelper::js('cart') ?>"></script>