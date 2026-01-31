<?php
$customCss = '/public/src/css/dashboard/index.css';
ob_start();
?>

<div class="dashboard">
    <h1>Modifier une technologie</h1>

    <!-- Formulaire de modification de technologie -->
    <form data-api-endpoint="/techno/<?= htmlspecialchars($technoCode) ?>" data-api-method="PUT">
        <label for="techno-code">Code :</label>
        <input type="text" id="techno-code" name="code" value="<?= htmlspecialchars($technoCode) ?>" readonly disabled>
    
        <label for="techno-libelle">Nom de la technologie :</label>
        <input type="text" id="techno-libelle" name="libelle" required>
    
        <label for="techno-color">Couleur :</label>
        <input type="color" id="techno-color" name="color" value="#2563eb" required>
    
        <button type="submit">Modifier la technologie</button>
    </form>
</div>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>
