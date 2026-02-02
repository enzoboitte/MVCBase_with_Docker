<?
$customCss[] = '/public/src/css/dashboard/index.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <!-- Formulaire de modification de l'utilisateur -->
    <form id="editUserForm" data-api-endpoint="/user/<?= $userId ?>" data-api-method="PUT" data-api-action="onUserUpdated">
        <h3>Modifier l'utilisateur</h3>
        <div class="form-group">
            <label for="user-username">Nom d'utilisateur :</label>
            <input type="text" id="user-username" name="name" required>
        </div>
        <div class="form-group">
            <label for="user-email">Email :</label>
            <input type="email" id="user-email" name="email" required>
        </div>

        <!-- changement de mot de passe optionnel -->
        <div class="form-group">
            <label for="user-password">Nouveau mot de passe (laisser vide pour ne pas changer) :</label>
            <input type="password" id="user-password" name="password">
        </div>

        <button type="submit">Mettre à jour l'utilisateur</button>
    </form>
</main>

<?php
$content = ob_get_clean();
$customJs = '/public/src/js/dashboard/user.js';
require ROOT . '/app/views/layout.php';
?>