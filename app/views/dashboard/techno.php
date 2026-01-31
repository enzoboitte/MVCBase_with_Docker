<?php
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <h2>Gestion des technologies</h2>
    <!-- Liste des technologies -->
    <table id="technoList" data-api-endpoint="/techno" data-api-method="GET" data-edit-url="/dashboard/technologies/edit">
        <thead>
            <tr></tr>
        </thead>
        <tbody></tbody>
    </table>
    
    
    <!-- Formulaire d'ajout de technologie -->
    <form data-api-endpoint="/techno" data-api-method="POST" data-api-action="reloadTable">
        <label for="techno-code">Code (optionnel) :</label>
        <input type="text" id="techno-code" name="code" placeholder="Ex: REACT, VUE, PHP...">
    
        <label for="techno-libelle">Nom de la technologie :</label>
        <input type="text" id="techno-libelle" name="libelle" required>
    
        <label for="techno-color">Couleur :</label>
        <input type="color" id="techno-color" name="color" value="#2563eb" required>
    
        <button type="submit">Ajouter la technologie</button>
    </form>
    
    <script>
        async function reloadTable(e, data)
        {
            await handleTable(document.getElementById('technoList'));
        }
    </script>
</main>
<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
