<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MVC PHP' ?></title>
    <link rel="stylesheet" href="/public/src/css/style.css">
    <?php if (isset($customCss)):
        if (is_array($customCss)):
            foreach ($customCss as $cssPath): ?>
                <link rel="stylesheet" href="<?= $cssPath ?>">
            <?php endforeach;
        else: ?>
            <link rel="stylesheet" href="<?= $customCss ?>">
        <?php endif;
    endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body<?= isset($bodyClass) ? ' class="' . $bodyClass . '"' : '' ?>>
    <?php
    if(isset($back)): ?>
        <a href="<?= htmlspecialchars($back) ?>" class="back-button">← Retour</a>
    <?php endif; ?>
    <?= $content ?? '' ?>
    <!--<nav>
        <a href="/">Accueil</a>
        <a href="/about">À propos</a>
        <a href="/user/42">User 42</a>
    </nav>
    
    <main>
        <?= '' //$content ?? '' ?>
    </main>

    <div>
        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-labelledby="t">
            <image href="https://images.unsplash.com/photo-1488161628813-04466f872be2?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=764&q=80"
                width="200" height="200"
                preserveAspectRatio="xMidYMid slice"
                clip-path="url(#blobClip)"/>
            <clipPath id="blobClip">
            <path d="M43.1,-68.5C56.2,-58.6,67.5,-47.3,72.3,-33.9C77.2,-20.5,75.5,-4.9,74.2,11.3C72.9,27.6,71.9,44.5,63.8,57.2C55.7,69.8,40.6,78.2,25.5,79.2C10.4,80.1,-4.7,73.6,-20.9,69.6C-37.1,65.5,-54.5,63.9,-66,54.8C-77.5,45.8,-83.2,29.3,-85.7,12.3C-88.3,-4.8,-87.7,-22.3,-79.6,-34.8C-71.5,-47.3,-55.8,-54.9,-41.3,-64.2C-26.7,-73.6,-13.4,-84.7,0.8,-86C15,-87.2,29.9,-78.5,43.1,-68.5Z"
                    transform="translate(100 100)"/>
            </clipPath>

        <path
            class="blob"
            d="M43.1,-68.5C56.2,-58.6,67.5,-47.3,72.3,-33.9C77.2,-20.5,75.5,-4.9,74.2,11.3C72.9,27.6,71.9,44.5,63.8,57.2C55.7,69.8,40.6,78.2,25.5,79.2C10.4,80.1,-4.7,73.6,-20.9,69.6C-37.1,65.5,-54.5,63.9,-66,54.8C-77.5,45.8,-83.2,29.3,-85.7,12.3C-88.3,-4.8,-87.7,-22.3,-79.6,-34.8C-71.5,-47.3,-55.8,-54.9,-41.3,-64.2C-26.7,-73.6,-13.4,-84.7,0.8,-86C15,-87.2,29.9,-78.5,43.1,-68.5Z"
            transform="translate(100 100)"
                        fill="url(#imgFill)"
        />
        <path
            id="text"    d="M43.1,-68.5C56.2,-58.6,67.5,-47.3,72.3,-33.9C77.2,-20.5,75.5,-4.9,74.2,11.3C72.9,27.6,71.9,44.5,63.8,57.2C55.7,69.8,40.6,78.2,25.5,79.2C10.4,80.1,-4.7,73.6,-20.9,69.6C-37.1,65.5,-54.5,63.9,-66,54.8C-77.5,45.8,-83.2,29.3,-85.7,12.3C-88.3,-4.8,-87.7,-22.3,-79.6,-34.8C-71.5,-47.3,-55.8,-54.9,-41.3,-64.2C-26.7,-73.6,-13.4,-84.7,0.8,-86C15,-87.2,29.9,-78.5,43.1,-68.5Z"
            transform="translate(100 100)"
            fill="none" stroke="none"
            pathLength="100"
        />

        <text class="text-content">
            <textPath href="#text" startOffset="0%">WORK IN PROGRESS • WORK IN PROGRESS • WORK IN PROGRESS • WORK IN PROGRESS • WORK IN PROGRESS
                <animate attributeName="startOffset" from="0%" to="-50%" dur="15s" repeatCount="indefinite" />
            </textPath>
        </text>
        </svg>
    </div>-->

    <script src="/public/src/js/app.js"></script>
    <?php if (isset($customJs)): ?>
    <script src="<?= $customJs ?>"></script>
    <?php endif; ?>
</body>
</html>
