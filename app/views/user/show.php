<?php ob_start(); ?>

<h1><?= htmlspecialchars($title) ?></h1>
<p>Affichage de l'utilisateur #<?= htmlspecialchars($userId) ?></p>
<a href="/user/<?= htmlspecialchars($userId) ?>/edit">Modifier</a>

<?php $content = ob_get_clean(); ?>
<?php require ROOT . '/app/views/layout.php'; ?>
