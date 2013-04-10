# bramus/router

A lightweight and simple object oriented PHP Router.
Built by Bram(us) Van Damme - [http://www.bram.us](http://www.bram.us)

[![Build Status](https://secure.travis-ci.org/bramus/router.png)](http://travis-ci.org/bramus/router)


## Features

- Static Route Patterns
- Dynamic Route Patterns
- Optional Route Subpatterns
- `GET`, `POST`, `PUT`, `DELETE`, and `OPTIONS` request methods
- Custom 404 handling
- Before Route Middlewares
- Before Router Middlewares
- After Router Middleware (Finish Callback)
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



## Usage

Create an instance of `\Bramus\Router\Router`, define some routes onto it, and run it.

	// Require composer autoloader
	require __DIR__ . '/vendor/autoload.php';

	// Create Router instance
	$router = new \Bramus\Router\Router();

	// Define routes
	...

	// Run it!
	$router->run();


### Routing

Hook __routes__ (a combination of one or more HTTP methods and a pattern) using `$router->match(method(s), pattern, function)`:

	$router->match('GET|POST', 'pattern', function() { … });

`bramus/router` supports `GET`, `POST`, `PUT`, `DELETE`, and `OPTIONS` HTTP request methods. Pass in a single request method, or multiple request methods separated by `|`.

When a route matches, the attached __route handling function__ will be executed. Only the first route matched will be handled. When no matching route is found, an `'HTTP/1.1 404 Not Found'` status code will be returned.

Shorthands for single request methods are provided:

	$router->get('pattern', function() { … });
	$router->post('pattern', function() { … });
	$router->put('pattern', function() { … });
	$router->delete('pattern', function() { … });
	$router->options('pattern', function() { … });

Note: Routes must be hooked before `$router->run();` is being called.


### Route Patterns

Route patterns can be static or dynamic.
- __Static Route Patterns__ are essentially URIs, e.g. `/about`.
- __Dynamic Route Patterns__ are Perl-compatible regular expressions (PCRE) that resemble URIs, e.g. `/movies/(\d+)`

Commonly used subpatterns within Dynamic Route Patterns are:
- `\d+` = One or more digits (0-9)
- `\w+` = One or more word characters (a-z 0-9 _)
- `[a-z0-9_-]+` = One or more word characters (a-z 0-9 _) and the dash (-)
- `.*` = Any character (including `/`), zero or more
- `[^/]+` = Any character but `/`, one or more

Note: The [PHP PCRE Cheat Sheet](https://www.cs.washington.edu/education/courses/190m/12sp/cheat-sheets/php-regex-cheat-sheet.pdf) might come in handy.

The __subpatterns__ defined in Dynamic Route Patterns are converted to parameters which are passed into the route handling function. Prerequisite is that these subpatterns need to be defined as __parenthesized subpatterns__, which means that they should be wrapped between parens:

	// Bad
	$router->get('/hello/\w+', function($name) {
		echo 'Hello ' . htmlentities($name);
	});

	// Good
	$router->get('/hello/(\w+)', function($name) {
		echo 'Hello ' . htmlentities($name);
	});

Note: The leading `/` at the very beginning of a route pattern is not mandatory, but is recommended.

When multiple subpatterns are defined, they resulting __route handling parameters__ are passed into the route handling function in the order they are defined in:

	$router->get('/movies/(\d+)/photos/(\d+)', function($movieId, $photoId) {
		echo 'Movie #' . $movieId . ', photo #' . $photoId);
	});


### Optional Route Subpatterns

Route subpatterns can be made optional by making the subpatterns optional by adding a `?` after them. Think of blog URLs in the form of `/blog(/year)(/month)(/day)(/slug)`:

	$router->get('/blog(/\d+)?(/\d+)?(/\d+)?(/[a-z0-9_-]+)?', function($year = null, $month = null, $day = null, $slug = null) {
		if (!$year) { echo 'Blog overview'; return; }
		if (!$month) { echo 'Blog year overview'; return; }
		if (!$day) { echo 'Blog month overview'; return; }
		if (!$slug) { echo 'Blog day overview'; return; }
		echo 'Blogpost ' . htmlentities($slug) . ' detail';
	});

The code snippet above responds to the URLs `/blog`, `/blog/year`, `/blog/year/month`, `/blog/year/month/day`, and `/blog/year/month/day/slug`.

Note: With optional parameters it is important that the leading `/` of the subpatterns is put inside the subpattern itself. Don't forget to set default values for the optional parameters.

The code snipped above unfortunately also responds to URLs like `/blog/foo` and states that the overview needs to be shown - which is incorrect. Optional subpatterns can be made successive by extending the parenthesized subpatterns so that they contain the other optional subpatterns: The pattern should resemble `/blog(/year(/month(/day(/slug))))` instead of the previous `/blog(/year)(/month)(/day)(/slug)`:

	$router->get('/blog(/\d+(/\d+(/\d+(/[a-z0-9_-]+)?)?)?)?', function($year = null, $month = null, $day = null, $slug = null) {
		// ...
	}

Note: It is highly recommended to __always__ define successive optional parameters.

To make things complete use [quantifiers](http://www.php.net/manual/en/regexp.reference.repetition.php) to require the correct amount of numbers in the URL:

	$router->get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function($year = null, $month = null, $day = null, $slug = null) {
		// ...
	}


### Custom 404

Override the default 404 handler using `$router->set404(function);`

	$router->set404(function() {
		header('HTTP/1.1 404 Not Found');
		// ... do something special here
	});

The 404 will be executed when no route pattern was matched to the current URL.


### Before Route Middlewares

`bramus/router` supports __Before Route Middlewares__, which are executed before the route handling is processed.

Like route handling functions, you hook a handling function to a combination of one or more HTTP request methods and a specific route pattern.

	$router->before('GET|POST', '/admin/.*', function() {
		if (!isset($_SESSION['user'])) {
			header('location: /auth/login');
			exit();
		}
	});

Unlike route handling functions, more than one before route middleware is executed when more than one route match is found.


### Before Router Middlewares

Before route middlewares are route specific. Using a general route pattern (viz. _all URLs_), they can become __Before Router Middlewares__ _(in other projects sometimes referred to as before app middlewares)_ which are always executed, no matter what the requested URL is.

	$router->before('GET', '/.*', function() {
		// ... this will always be executed
	});


### After Router Middleware / Run Callback

Run one (1) middleware function, name the __After Router Middleware__ _(in other projects sometimes referred to as after app middlewares)_ after the routing was processed. Just pass it along the `$router->run()` function. The run callback is route independent.

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

Given this structure it is still possible to manipulate the output from within the After Router Middleware


## A note on working with PUT

There's no such thing as `$_PUT` in PHP. One must fake it:

	$router->put('/movies/(\d+)', function($id) {

		// Fake $_PUT
		$_PUT  = array();
		parse_str(file_get_contents('php://input'), $_PUT);

		// ...

	});



## Unit Testing & Code Coverage

`bramus/router` ships with unit tests using [PHPUnit](https://github.com/sebastianbergmann/phpunit/).

- If PHPUnit is installed globally run `phpunit` to run the tests.

- If PHPUnit is not installed globally, install it locally throuh composer by running `composer install --dev`. Run the tests themselves by calling `vendor/bin/phpunit`.

  The included `composer.json` will also install `php-code-coverage` which allows one to generate a __Code Coverage Report__. Run `phpunit --coverage-html ./tests-report` (XDebug required), a report will be placed into the `tests-report` subfolder.


## Acknowledgements

`bramus/router` is inspired upon [Klein](https://github.com/chriso/klein.php) and [Ham](https://github.com/radiosilence/Ham). Whilst Klein provides lots of features it is not object oriented. Whilst Ham is Object Oriented, it's bad at _separation of concerns_ as it also provides templating within the routing class.



## License

`bramus/router` is released under the MIT public license. See the enclosed `LICENSE` for details.