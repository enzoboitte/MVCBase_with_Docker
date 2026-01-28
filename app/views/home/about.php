<?php ob_start(); ?>

<h1><?= htmlspecialchars($title) ?></h1>
<p>Page à propos du site.</p>

<?php $content = ob_get_clean(); ?>
<?php require ROOT . '/app/views/layout.php'; ?>
