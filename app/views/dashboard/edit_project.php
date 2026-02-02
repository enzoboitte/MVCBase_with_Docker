<?php
$customCss[] = '/public/src/css/dashboard/index.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <!-- Formulaire de modification -->
    <form id="editProjectForm" data-api-endpoint="/project/<?= $projectId ?>" data-api-method="PUT" data-api-action="onProjectUpdated">
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
    
        <button type="submit">Modifier le projet</button>
    </form>
    
    <!-- Gestion des images -->
    <div class="image-manager">
        <h2>Images du projet</h2>
        
        <div id="imageGallery" class="image-gallery">
            <!-- Les images seront chargées dynamiquement -->
        </div>
        
        <div class="upload-zone" id="uploadZone">
            <input type="file" id="project-images" name="images" accept="image/*" multiple style="display: none;">
            <label for="project-images" class="upload-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>Cliquer ou glisser-déposer des images</span>
                <small>PNG, JPG, GIF, WebP (max 5MB)</small>
            </label>
        </div>
    </div>
</main>

<script>
const projectId = <?= json_encode($projectId) ?>;

document.addEventListener('DOMContentLoaded', async () => {
    await loadProject();
    initImageUpload();
});
</script>

<?php
$content = ob_get_clean();
$customJs = '/public/src/js/dashboard/project.js';
$customCss[] = '/public/src/css/dashboard/project.css';
require ROOT . '/app/views/layout.php';
?>
