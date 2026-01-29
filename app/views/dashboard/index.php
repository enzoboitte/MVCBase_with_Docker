<?
ob_start();
?>

<div class="dashboard">
    <h1><?= htmlspecialchars($title) ?></h1>

    <ul>
        <li><a href="/dashboard/diploma">Gestion des diplômes</a></li>
        <li><a href="/dashboard/projects">Gestion des projets</a></li>
        <li><a href="/dashboard/technologies">Gestion des technologies</a></li>
        <li><a href="/dashboard/contact">Gestion des contacts</a></li>
    </ul>
</div>
<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>