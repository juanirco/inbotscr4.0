<?php
    // Cambie este valor en cada deploy importante para forzar recarga de CSS/JS
    $ASSET_VERSION = '2025-11-05-2';
?>
<!DOCTYPE html>
<html lang="e">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $title ?? ''; ?> | <?php echo $description ?? ''; ?>">
    
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">

    <title>Inbotscr<?php echo isset($title) ? ' | ' . $title : ''; ?></title>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&family=Space+Grotesk:wght@300;400;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&family=Space+Grotesk:wght@300;400;600;700&display=swap" rel="stylesheet">
    </noscript>

    <!-- CSS principal con versión de deploy -->
    <link rel="stylesheet" href="/../build/css/app.css?v=<?= $ASSET_VERSION ?>">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-T7W1RVDXJ4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-T7W1RVDXJ4');
    </script>
</head>
<body>
    <div class="container_background">
        <?php echo $content; ?>
        <?php include_once __DIR__ . '/pages/components/footer.php'; ?>

        <!-- JS principal con versión de deploy -->
        <script type="module" src="/build/js/app.js?v=<?= $ASSET_VERSION ?>"></script>

        <?php echo $script ?? ''; ?>
    </div>
</body>
</html>
