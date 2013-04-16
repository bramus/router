<?php

	// In case one is using PHP 5.4's built-in server
	$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
	if (php_sapi_name() === 'cli-server' && is_file($filename)) {
	    return false;
	}

	// Include the Router class
	// @note: it's recommended to just use the composer autoloader when working with other packages too
	require_once __DIR__ . '/../src/Bramus/Router/router.php';

	// Create a Router
	$router = new \Bramus\Router\Router();

	// Custom 404 Handler
	$router->set404(function() {
		header('HTTP/1.1 404 Not Found');
		echo '404, route not found!';
	});

	// Before Router Middleware
	$router->before('GET', '/.*', function() {
		header('X-Powered-By: bramus/router');
	});

	// Static route: / (homepage)
	$router->get('/', function() {
		echo '<h1>bramus/router</h1><p>Try these routes:<p><ul><li>/hello/<em>name</em></li><li>/blog</li><li>/blog/<em>year</em></li><li>/blog/<em>year</em>/<em>month</em></li><li>/blog/<em>year</em>/<em>month</em>/<em>day</em></li></ul>';
	});

	// Dynamic route: /hello/name
	$router->get('/hello/(\w+)', function($name) {
		echo 'Hello ' . htmlentities($name);
	});

	// Dynamic route with (successive) optional subpatterns: /blog(/year(/month(/day(/slug))))
	$router->get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function($year = null, $month = null, $day = null, $slug = null) {
		if (!$year) { echo 'Blog overview'; return; }
		if (!$month) { echo 'Blog year overview (' . $year . ')'; return; }
		if (!$day) { echo 'Blog month overview (' . $year . '-' . $month . ')'; return; }
		if (!$slug) { echo 'Blog day overview (' . $year . '-' . $month . '-' . $day . ')'; return; }
		echo 'Blogpost ' . htmlentities($slug) . ' detail (' . $year . '-' . $month . '-' . $day . ')';
	});

	// Thunderbirds are go!
	$router->run();

// EOF