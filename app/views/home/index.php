<?php ob_start(); ?>

<h1><?= htmlspecialchars($title) ?></h1>

<?php $content = ob_get_clean(); ?>
<?php require ROOT . '/app/views/layout.php'; ?>
