# bramus/router

A lightweight and simple object oriented PHP Router.
Built by Bram(us) Van Damme - [http://www.bram.us](http://www.bram.us)


## Features

- Dynamic route patterns
- `GET`, `POST`, `PUT`, `DELETE`, and `OPTIONS` request methods
- Before Route Middlewares
- Custom 404 handling
- Finish callback (After Router Middleware)
- Works fine in subfolders


## Prerequisites/Requirements

- PHP 5.3 or greater
- [URL Rewriting](https://gist.github.com/bramus/5332525)


## Installation

Installation is possible using Composer

	{
		"require": {
			"bramus/router": "dev-master"
		}
	}


## Demo

A demo is included in the `demo` subfolder. Serve it using your favorite web server, or using PHP 5.4's built-in server by executing `php -S localhost:8080` on the shell. A `.htaccess` for use with Apache is included.

Be sure to run `composer install` before trying to run the demo.


## Usage

Create an instance of `\Bramus\Router\Router`, define some routes onto it, and run it.

	// Create router instance
	$router = new \Bramus\Router\Router();

	// Define routes
	...

	// Run it!
	$router->run();


### Routing

Hook routes using `$router->match(method(s), pattern, function)`:

	$router->match('GET|POST', 'pattern', function() { … });

`bramus/router` supports `GET`, `POST`, `PUT`, `DELETE`, and `OPTIONS` HTTP request methods. Pass in a single request method, or multiple request methods separated by `|`.

Shorthands for single request methods are provided:

	$router->get('pattern', function() { … });
	$router->post('pattern', function() { … });
	$router->put('pattern', function() { … });
	$router->delete('pattern', function() { … });
	$router->options('pattern', function() { … });

Note: Routes must be hooked before `$router->run();` is being called.


### Route Patterns

Route patterns, basically URIs, can be static or dynamic.

Allowed dynamic parts are:
- `\d+` = One or more digits (0-9)
- `\w+` = One or more word characters (a-z 0-9 _)
- `.*` = Any character (including `/`), zero or more

The dynamic parts are converted to route variables and passed into the handling function. Examples

	$router->get('/movies/\d+', function($movieId) {
		echo 'Movie #' . $movieId . ' detail';
	});

	$router->get('/movies/\d+/photos/\d+', function($movieId, $photoId) {
		echo 'Movie #' . $movieId . ', photo #' . $photoId);
	});

Only the first route matched will be handled. When no matching route is found, an `'HTTP/1.1 404 Not Found'` status code will be returned.

The leading `/` is not mandatory, but attests good coding style.

### Custom 404

Override the default 404 handler using `$router->set404(function);`

	$router->set404(function() {
		header('HTTP/1.1 404 Not Found');
		// ... do something special here
	});

The 404 will be executed when no route pattern was matched to the current URL.


### Middlewares

`bramus/router` supports _before route middlewares_, which are executed before the route handling is processed.

Like route handling functions, you hook a before route middleware to a combination of one or more HTTP request methods and a specific route pattern.

	$router->before('GET|POST', '/admin/.*', function() {
		if (!isset($_SESSION['user'])) {
			header('location: /auth/login');
			exit();
		}
	});

Unlike route handling functions, more than one before route middleware is executed when more than one route match is found.

### Run Callback / After Router Middleware

Run one (1) middleware function after the routing was processed. Just pass it along the `$router->run()` function. The run callback is route independent.

	$router->run(function() { … });

Note: If the route handling function has `exit()`ed the run callback won't be run.

## Integration with other libraries

Integrate other libraries with `bramus/router` by making good use of the `use` keyword to pass dependencies into the handling functions.

	$tpl = new \Acme\Template\Template();

	$router->get('/', function() use ($tpl) {
		$tpl->load('home.tpl');
		$tpl->setdata(array(
			'name' => 'Bramus!'
		));
	});

	$router->run(function() use ($tpl) {
    	$tpl->display();
	});


## A note on working with PUT

There's no such thing as `$_PUT` in PHP. One must fake it:

	$router->put('/movies/\d+', function() {

		// Fake $_PUT
		$_PUT  = array();
		parse_str(file_get_contents('php://input'), $_PUT);

		// …

	});


## Acknowledgements

`bramus/router` is inspired upon [Klein](https://github.com/chriso/klein.php) and [Ham](https://github.com/radiosilence/Ham). Whilst Klein provides lots of features it is not object oriented. Whilst Ham is Object Oriented, it's bad at _separation of concerns_ as it also provides templating within the routing class.


## License

`bramus/router` is released under the MIT public license. See the enclosed `LICENSE` for details.