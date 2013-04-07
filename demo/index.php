<?php

	// In case one is using PHP 5.4's built-in server
	$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
	if (php_sapi_name() === 'cli-server' && is_file($filename)) {
	    return false;
	}

	// Include the Composer autoloader
	require_once __DIR__ . '/vendor/autoload.php';

	// Create a Router
	$router = new \Bramus\Router\Router();

	// Custom 404 Handler
	$router->set404(function() {
		header('HTTP/1.1 404 Not Found');
		echo '404, route not found!';
	});

	// Homepage route
	$router->get('/', function() {
		echo 'Ohai! Surf to <code>/hello/name</code> to get your mojo on.';
	});

	// Hello route
	$router->get('/hello/\w+', function($name) {
		echo 'Hello ' . htmlentities($name);
	});

	// Thunderbirds are go!
	$router->run();

// EOF