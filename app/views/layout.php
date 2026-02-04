<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'FinanceApp' ?></title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body<?= isset($bodyClass) ? ' class="' . $bodyClass . '"' : '' ?>>
    <?= $content ?? '' ?>

    <script src="/public/src/js/app.js"></script>
    <?php if (isset($customJs)): ?>
    <script src="<?= $customJs ?>"></script>
    <?php endif; ?>
</body>
</html>

