<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Café-VT' ?></title>
    
    <?php 
    // Asegurarnos de que las clases helper estén disponibles
    require_once BASE_PATH . '/app/helpers/AssetHelper.php';
    require_once BASE_PATH . '/app/helpers/ViewHelper.php';
    ?>
    
    <!-- CSS Base -->
    <link rel="stylesheet" href="<?= AssetHelper::css('common') ?>">
    
    <!-- CSS Específicos -->
    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $stylesheet): ?>
            <link rel="stylesheet" href="<?= AssetHelper::css($stylesheet) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Incluir header -->
    <?php require_once BASE_PATH . '/app/views/partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <!-- Incluir footer -->
    <?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>

    <!-- JavaScript -->
    <?php if (isset($js) && is_array($js)): ?>
        <?php foreach ($js as $script): ?>
            <script src="<?= AssetHelper::js($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>