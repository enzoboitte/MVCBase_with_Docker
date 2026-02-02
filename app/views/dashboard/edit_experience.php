<?php
$customCss[] = '/public/src/css/dashboard/index.css';
$customCss[] = '/public/src/css/dashboard/experience.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <!-- Formulaire de modification -->
    <form id="editExperienceForm" data-api-endpoint="/experience/<?= $experienceId ?>" data-api-method="PUT" data-api-action="onExperienceUpdated">
        <h3>Modifier l'expérience</h3>
        
        <div class="form-row">
            <div class="form-group">
                <label for="exp-title">Poste / Titre :</label>
                <input type="text" id="exp-title" name="title" required>
            </div>
            <div class="form-group">
                <label for="exp-company">Entreprise :</label>
                <input type="text" id="exp-company" name="company" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="exp-location">Localisation :</label>
                <input type="text" id="exp-location" name="location" required>
            </div>
            <div class="form-group">
                <label for="exp-contract">Type de contrat :</label>
                <select id="exp-contract" name="contract_type_id" required>
                    <option value="">Sélectionner...</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="exp-start">Date de début :</label>
                <input type="month" id="exp-start" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="exp-end">Date de fin :</label>
                <input type="month" id="exp-end" name="end_date">
                <small class="form-hint">Laisser vide si poste actuel</small>
            </div>
        </div>
        
        <label for="exp-description">Description :</label>
        <textarea id="exp-description" name="description" required></textarea>
        
        <label>Technologies utilisées :</label>
        <div class="techno-multiselect">
            <div class="techno-selected" id="techno-selected">
                <span class="techno-placeholder">Sélectionner des technologies...</span>
            </div>
            <div class="techno-dropdown" id="techno-dropdown">
                <input type="text" class="techno-search" id="techno-search" placeholder="Rechercher...">
                <div class="techno-options" id="techno-options"></div>
            </div>
        </div>
        <input type="hidden" id="exp-technologies" name="technologies">
        
        <label>Missions principales :</label>
        <div class="tasks-container" id="tasks-container">
            <!-- Les tâches seront chargées dynamiquement -->
        </div>
        <input type="hidden" id="exp-tasks" name="tasks">
        
        <button type="submit">Enregistrer les modifications</button>
    </form>
</main>

<script>
    const experienceId = <?= json_encode($experienceId) ?>;
</script>

<?php
$content = ob_get_clean();
$customJs = '/public/src/js/dashboard/experience.js';
require ROOT . '/app/views/layout.php';
?>
