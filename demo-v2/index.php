<?php

require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../src/Bramus/Router/Router.php';
// require_once __DIR__ . '/../src/amireshoon/HaloRouter/HaloRouter.php';

// Create a Router
$router = new \Halo\Router();

$router->get('/', function( $request ) {
    echo $request->getMethod();
});

$router->run();