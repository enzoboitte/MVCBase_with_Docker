<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MVC PHP' ?></title>
    <!--<link rel="stylesheet" href="/public/src/css/style.css">-->
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
    <link rel="stylesheet" href="/public/src/css/style.css">
</head>
<body<?= isset($bodyClass) ? ' class="' . $bodyClass . '"' : '' ?>>
    <?php
    if(isset($back)): ?>
        <a href="<?= htmlspecialchars($back) ?>" class="back-button">← Retour</a>
    <?php endif; ?>
    <?= $content ?? '' ?>

    <div id="transactions_add" class="popup">
        <div class="popup_content">
            <div class="popup_header">
                <h2 class="popup_title" id="popup-project-title"></h2>
                <span class="popup_close">×</span>
            </div>
            <div class="popup_body">
                <div id="popup-project-images"></div>
                <div class="popup-images-content">
                    <div id="popup-images-nav" class="popup-images-nav"></div>
                </div>
                <div class="popup-content-wrapper">
                    <p id="popup-project-description"></p>
                    <div id="popup-project-techs"></div>
                    <div id="popup-project-link"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="/public/src/js/app.js"></script>
    <?php if (isset($customJs)): ?>
    <script src="<?= $customJs ?>"></script>
    <?php endif; ?>
</body>
</html>
