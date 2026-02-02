<?php
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>

<div class="dashboard">
    <h1>Modifier une compétence</h1>

    <!-- Formulaire de modification de compétence -->
    <form data-api-endpoint="/competence/<?= htmlspecialchars($competenceId) ?>" data-api-method="PUT">
        <label for="category_id">Catégorie :</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Sélectionner une catégorie --</option>
        </select>
    
        <label for="techno_code">Technologie :</label>
        <select id="techno_code" name="techno_code" required>
            <option value="">-- Sélectionner une technologie --</option>
        </select>
    
        <button type="submit">Modifier la compétence</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Charger les catégories
        try {
            const catResponse = await apiRequest('GET', '/competence/category');
            const categorySelect = document.getElementById('category_id');
            if (catResponse.data) {
                catResponse.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Failed to load categories:', error);
        }

        // Charger les technologies
        try {
            const techResponse = await apiRequest('GET', '/competence/technos/available');
            const technoSelect = document.getElementById('techno_code');
            if (techResponse.data) {
                techResponse.data.forEach(tech => {
                    const option = document.createElement('option');
                    option.value = tech.code;
                    option.textContent = tech.libelle;
                    technoSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Failed to load technologies:', error);
        }

        // Charger les données de la compétence et pré-remplir le formulaire
        try {
            const response = await apiRequest('GET', '/competence/<?= htmlspecialchars($competenceId) ?>');
            if (response.data) {
                document.getElementById('category_id').value = response.data.category_id;
                document.getElementById('techno_code').value = response.data.techno_code;
            }
        } catch (error) {
            console.error('Failed to load competence data:', error);
        }
    });
</script>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>
