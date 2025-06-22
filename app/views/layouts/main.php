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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Específicos -->
    <?php if (isset($css) && is_array($css)): ?>
        <?php foreach ($css as $stylesheet): ?>
            <link rel="stylesheet" href="<?= AssetHelper::css($stylesheet) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <script>
        // Debug: Log del usuario actual
        console.log('🔍 DEBUG: Layout main.php - $_SESSION[user_id]:', <?= json_encode($_SESSION['user_id'] ?? 'null') ?>);
        console.log('🔍 DEBUG: Layout main.php - $_SESSION[correo]:', <?= json_encode($_SESSION['correo'] ?? 'null') ?>);
        
        window.currentUserId = <?= $_SESSION['user_id'] ?? 'null' ?>;
        
        // Debug: Log de la variable JavaScript
        console.log('🔍 DEBUG: Layout main.php - window.currentUserId:', window.currentUserId);
    </script>
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