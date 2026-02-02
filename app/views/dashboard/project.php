<?php
$customCss[] = '/public/src/css/dashboard/index.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <h2>Gestion des projets</h2>
    <!-- Liste des projets -->
    <table id="projectList" data-api-endpoint="/project" data-api-method="GET" data-edit-url="/dashboard/projects/edit">
        <thead>
            <tr></tr>
        </thead>
        <tbody></tbody>
    </table>
    
    
    <!-- Formulaire d'ajout de projet -->
    <form id="projectForm" data-api-endpoint="/project" data-api-method="POST" data-api-action="onProjectCreated">
        <label for="project-name">Nom du projet :</label>
        <input type="text" id="project-name" name="name" required>
    
        <label for="project-description">Description :</label>
        <textarea id="project-description" name="description" required></textarea>
    
        <label for="project-link">Lien (GitHub, GitLab, Site web...) :</label>
        <input type="url" id="project-link" name="link" placeholder="https://github.com/...">
    
        <label>Technologies :</label>
        <div class="techno-multiselect">
            <div class="techno-selected" id="techno-selected">
                <span class="techno-placeholder">Sélectionner des technologies...</span>
            </div>
            <div class="techno-dropdown" id="techno-dropdown">
                <input type="text" class="techno-search" id="techno-search" placeholder="Rechercher...">
                <div class="techno-options" id="techno-options">
                    <!-- Options chargées dynamiquement -->
                </div>
            </div>
        </div>
        <input type="hidden" id="project-technologies" name="technologies">
    
        <button type="submit">Ajouter le projet</button>
    </form>
</main>

<?php
$content = ob_get_clean();
$customJs = '/public/src/js/dashboard/project.js';
$customCss[] = '/public/src/css/dashboard/project.css';
require ROOT . '/app/views/layout.php';
?>
