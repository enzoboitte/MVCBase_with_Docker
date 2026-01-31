<?
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>
<div class="dashboard">
    <h1>Modifier un diplôme</h1>

    <!-- formulaire d'ajout de diplome -->
    <form data-api-endpoint="/diploma/<?= htmlspecialchars($diplomaId) ?>" data-api-method="PUT">
        <label for="degree-name">Nom du diplôme :</label>
        <input type="text" id="degree-name" name="name" required>
    
        <label for="institution">Établissement :</label>
        <input type="text" id="institution" name="school" required>
    
        <label for="country">Pays :</label>
        <input type="text" id="country" name="country" required>
    
        <label for="description">Description :</label>
        <textarea id="description" name="description"></textarea>
    
        <label for="start_year">Année de début :</label>
        <input type="number" id="start_year" name="start_at" required>
    
        <label for="end_year">Année de fin :</label>
        <input type="text" id="end_year" name="end_at" required>
    
        <button type="submit">Modifier le diplôme</button>
    </form>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>