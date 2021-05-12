<?php

    // In case one is using PHP 5.4's built-in server
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

    // Include the Router class
    // @note: it's recommended to just use the composer autoloader when working with other packages too
    require_once __DIR__ . '/../src/Bramus/Router/Router.php';

    // Create a Router
    $router = new \Bramus\Router\Router();

    // Custom 404 Handler
    $router->set404(function () {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '404, route not found!';
    });

    // custom 404
    $router->set404('/test(/.*)?', function () {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '<h1><mark>404, route not found!</mark></h1>';
    });

    $router->set404('/api(/.*)?', function() {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: application/json');

        $jsonArray = array();
        $jsonArray['status'] = "404";
        $jsonArray['status_text'] = "route not defined";

        echo json_encode($jsonArray);
    });

    // Before Router Middleware
    $router->before('GET', '/.*', function () {
        header('X-Powered-By: bramus/router');
    });

    // Static route: / (homepage)
    $router->get('/', function () {
        echo '<h1>bramus/router</h1>
              <p>Try these routes:<p>
              <ul>
                <li><a href="/hello/joe">/hello/<em>name</em></a></li>
                <li><a href="/blog">/blog</a></li>
                <li><a href="/blog/'.date('Y').'">/blog/<em>year</em></a></li>
                <li><a href="/blog/'.date('Y').'/'.date('m').'">/blog/<em>year</em>/<em>month</em></a></li>
                <li><a href="/blog/'.date('Y').'/'.date('m').'/'.date('d').'">/blog/<em>year</em>/<em>month</em>/<em>day</em></a></li>
                <li><a href="/movies">/movies</a></li>
                <li><a href="/movies/23">/movies/<em>id</em></a></li>
              </ul>
              <br><br>
              <p>Custom error routes</p>
              <ul>
                <li><a href="/something">/*</a> <em>Normal 404</em></li>
                <li><a href="/test">/test/*</a> <em>Custom 404</em></li>
                <li><a href="/api/getUser">/api/getUser</a> <em>API 404</em></li>
              </ul>
        ';
    });

    // Static route: /hello
    $router->get('/hello', function () {
        echo '<h1>bramus/router</h1><p>Visit <code>/hello/<em>name</em></code> to get your Hello World mojo on!</p>';
    });

    // Dynamic route: /hello/name
    $router->get('/hello/(\w+)', function ($name) {
        echo 'Hello ' . htmlentities($name);
    });

    // Dynamic route: /ohai/name/in/parts
    $router->get('/ohai/(.*)', function ($url) {
        echo 'Ohai ' . htmlentities($url);
    });

    // Dynamic route with (successive) optional subpatterns: /blog(/year(/month(/day(/slug))))
    $router->get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function ($year = null, $month = null, $day = null, $slug = null) {
        if (!$year) {
            echo 'Blog overview';

            return;
        }
        if (!$month) {
            echo 'Blog year overview (' . $year . ')';

            return;
        }
        if (!$day) {
            echo 'Blog month overview (' . $year . '-' . $month . ')';

            return;
        }
        if (!$slug) {
            echo 'Blog day overview (' . $year . '-' . $month . '-' . $day . ')';

            return;
        }
        echo 'Blogpost ' . htmlentities($slug) . ' detail (' . $year . '-' . $month . '-' . $day . ')';
    });

    // Subrouting
    $router->mount('/movies', function () use ($router) {

        // will result in '/movies'
        $router->get('/', function () {
            echo 'movies overview';
        });

        // will result in '/movies'
        $router->post('/', function () {
            echo 'add movie';
        });

        // will result in '/movies/id'
        $router->get('/(\d+)', function ($id) {
            echo 'movie id ' . htmlentities($id);
        });

        // will result in '/movies/id'
        $router->put('/(\d+)', function ($id) {
            echo 'Update movie id ' . htmlentities($id);
        });
    });

    // Thunderbirds are go!
    $router->run();

// EOF
