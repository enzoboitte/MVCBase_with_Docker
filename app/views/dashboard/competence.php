<?php
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>

<main class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>
    
    <h2>Compétences par catégorie</h2>
    <!-- Liste des compétences -->
    <table id="competenceList" data-api-endpoint="/competence" data-api-method="GET" data-edit-url="/dashboard/competences/edit">
        <thead>
            <tr></tr>
        </thead>
        <tbody></tbody>
    </table>
    
    
    <!-- Formulaire d'ajout de compétence -->
    <h2>Ajouter une compétence</h2>
    <form data-api-endpoint="/competence" data-api-method="POST" data-api-action="reloadTable">
        <label for="category_id">Catégorie :</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Sélectionner une catégorie --</option>
        </select>
    
        <label for="techno_code">Technologie :</label>
        <select id="techno_code" name="techno_code" required>
            <option value="">-- Sélectionner une technologie --</option>
        </select>
    
        <button type="submit">Ajouter la compétence</button>
    </form>
    
    <script>
        async function reloadTable(e, data)
        {
            await handleTable(document.getElementById('competenceList'));
        }

        // Charger les catégories et technologies au chargement
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
        });
    </script>
</main>
<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
