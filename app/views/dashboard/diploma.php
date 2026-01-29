<?php
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <h2>Gestion des diplômes</h2>
    <!-- Liste des diplômes -->
    <table id="diplomaList" data-api-endpoint="/diploma" data-api-method="GET">
        <thead>
            <tr></tr>
        </thead>
        <tbody></tbody>
    </table>
    
    
    <!-- formulaire d'ajout de diplome -->
    <form data-api-endpoint="/diploma" data-api-method="POST" data-api-action="reloadTable">
        <label for="degree-name">Nom du diplôme :</label>
        <input type="text" id="degree-name" name="name" required>
    
        <label for="institution">Établissement :</label>
        <input type="text" id="institution" name="school" required>
    
        <label for="country">Pays :</label>
        <input type="text" id="country" name="country" required>
    
        <label for="description">Description :</label>
        <textarea id="description" name="description" required></textarea>
    
        <label for="start_year">Année de début :</label>
        <input type="number" id="start_year" name="start_at" required>
    
        <label for="end_year">Année de fin :</label>
        <input type="number" id="end_year" name="end_at" required>
    
        <button type="submit">Ajouter le diplôme</button>
    </form>
    
    <script>
        async function reloadTable(e, data)
        {
            await handleTable(document.getElementById('diplomaList'));
        }
    </script>
</main>
<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';