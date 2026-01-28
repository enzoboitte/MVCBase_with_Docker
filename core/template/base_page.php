<?
ob_start();
?>

<h1>{{ path_file }}</h1>

<?php
$content = ob_get_clean();
require ROOT . '/app/views/layout.php';
?>