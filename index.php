<?php
define('ROOT', __DIR__);

//require_once ROOT . '/app/models/Model.php';
//Model::F_vInitBDD();

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
