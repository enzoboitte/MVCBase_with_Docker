<?php ob_start(); ?>

<h1><?= htmlspecialchars($title) ?></h1>
<p>Édition de l'utilisateur #<?= htmlspecialchars($userId) ?></p>

<form>
    <input type="text" name="name" placeholder="Nom">
    <button type="submit">Enregistrer</button>
</form>

<?php $content = ob_get_clean(); ?>
<?php require ROOT . '/app/views/layout.php'; ?>
