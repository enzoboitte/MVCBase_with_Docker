<?php
define('ROOT', __DIR__);

require_once ROOT . '/app/models/Model.php';

$l_sBdd = getenv('DB_DATABASE') ?: 'portfolio';
$l_cCon = Model::getConnection();
// si la BDD n'existe pas, la créer
$sql = "SHOW DATABASES LIKE '$l_sBdd'";
$stmt = $l_cCon->query($sql);
if ($stmt->rowCount() === 0) 
{
    Model::F_vInitBDD();
}

$sql = "SHOW TABLES";
$stmt = $l_cCon->query($sql);
if ($stmt->rowCount() === 0) 
{
    Model::F_vInitBDD();
}

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        ROOT . '/app/controllers/',
        ROOT . '/app/models/',
        ROOT . '/core/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Charger le Router (nécessaire pour CRoute et RouteScanner)
require_once ROOT . '/core/Router.php';

// Scanner automatiquement les contrôleurs pour les attributs CRoute
RouteScanner::scan(ROOT . '/app/controllers');
Router::dispatch();
