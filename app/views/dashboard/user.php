<?
$customCss[] = '/public/src/css/dashboard/index.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <h2>Gestion des utilisateurs</h2>

    <!-- Liste des utilisateurs -->
    <table id="userList" data-api-endpoint="/user" data-api-method="GET" data-edit-url="/dashboard/users/edit">
        <thead>
            <tr></tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Formulaire d'ajout d'utilisateur -->
    <form id="userForm" data-api-endpoint="/user" data-api-method="POST" data-api-action="onUserCreated">
        <label for="user-username">Nom d'utilisateur :</label>
        <input type="text" id="user-username" name="name" required>
        <label for="user-email">Email :</label>
        <input type="email" id="user-email" name="email" required>
        <label for="user-password">Mot de passe :</label>
        <input type="password" id="user-password" name="password" required>
        <button type="submit">Ajouter l'utilisateur</button>
    </form>
</main>

<?php
$content = ob_get_clean();
$customJs = '/public/src/js/dashboard/user.js';
require ROOT . '/app/views/layout.php';
?>