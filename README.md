# bramus/router

[![Build Status](https://img.shields.io/travis/bramus/router.svg?style=flat-square)](http://travis-ci.org/bramus/router) [![Source](http://img.shields.io/badge/source-bramus/router-blue.svg?style=flat-square)](https://github.com/bramus/router) [![Version](https://img.shields.io/packagist/v/bramus/router.svg?style=flat-square)](https://packagist.org/packages/bramus/router) [![Downloads](https://img.shields.io/packagist/dt/bramus/router.svg?style=flat-square)](https://packagist.org/packages/bramus/router/stats) [![License](https://img.shields.io/packagist/l/bramus/router.svg?style=flat-square)](https://github.com/bramus/router/blob/master/LICENSE)

A lightweight and simple object oriented PHP Router.
Built by Bram(us) Van Damme _([https://www.bram.us](https://www.bram.us))_ and [Contributors](https://github.com/bramus/router/graphs/contributors)


## Features

- Supports `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`, `PATCH` and `HEAD` request methods
- [Routing shorthands such as `get()`, `post()`, `put()`, …](#routing-shorthands)
- [Static Route Patterns](#route-patterns)
- Dynamic Route Patterns: [Dynamic PCRE-based Route Patterns](#dynamic-pcre-based-route-patterns) or [Dynamic Placeholder-based Route Patterns](#dynamic-placeholder-based-route-patterns)
- [Optional Route Subpatterns](#optional-route-subpatterns)
- [Supports `X-HTTP-Method-Override` header](#overriding-the-request-method)
- [Subrouting / Mounting Routes](#subrouting--mounting-routes)
- [Allowance of `Class@Method` calls](#classmethod-calls)
- [Custom 404 handling](#custom-404)
- [Before Route Middlewares](#before-route-middlewares)
- [Before Router Middlewares / Before App Middlewares](#before-router-middlewares)
- [After Router Middleware / After App Middleware (Finish Callback)](#after-router-middleware--run-callback)
- [Works fine in subfolders](#subfolder-support)



## Prerequisites/Requirements

- PHP 5.3 or greater
- [URL Rewriting](https://gist.github.com/bramus/5332525)



## Installation

Installation is possible using Composer

```
composer require bramus/router ~1.5
```



## Demo

A demo is included in the `demo` subfolder. Serve it using your favorite web server, or using PHP 5.4+'s built-in server by executing `php -S localhost:8080` on the shell. A `.htaccess` for use with Apache is included.

Additionally a demo of a mutilingual router is also included. This can be found in the `demo-multilang` subfolder and can be ran in the same manner as the normal demo.

## Usage

Create an instance of `\Bramus\Router\Router`, define some routes onto it, and run it.

```php
// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define routes
// ...

// Run it!
$router->run();
```


### Routing

Hook __routes__ (a combination of one or more HTTP methods and a pattern) using `$router->match(method(s), pattern, function)`:

```php
$router->match('GET|POST', 'pattern', function() { … });
```

`bramus/router` supports `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `HEAD` _(see [note](#a-note-on-making-head-requests))_, and `OPTIONS` HTTP request methods. Pass in a single request method, or multiple request methods separated by `|`.

When a route matches against the current URL (e.g. `$_SERVER['REQUEST_URI']`), the attached __route handling function__ will be executed. The route handling function must be a [callable](http://php.net/manual/en/language.types.callable.php). Only the first route matched will be handled. When no matching route is found, a 404 handler will be executed.

### Routing Shorthands

Shorthands for single request methods are provided:

```php
$router->get('pattern', function() { /* ... */ });
$router->post('pattern', function() { /* ... */ });
$router->put('pattern', function() { /* ... */ });
$router->delete('pattern', function() { /* ... */ });
$router->options('pattern', function() { /* ... */ });
$router->patch('pattern', function() { /* ... */ });
```

You can use this shorthand for a route that can be accessed using any method:

```php
$router->all('pattern', function() { … });
```

Note: Routes must be hooked before `$router->run();` is being called.

Note: There is no shorthand for `match()` as `bramus/router` will internally re-route such requrests to their equivalent `GET` request, in order to comply with RFC2616 _(see [note](#a-note-on-making-head-requests))_.

### Route Patterns

Route Patterns can be static or dynamic:

- __Static Route Patterns__ contain no dynamic parts and must match exactly against the `path` part of the current URL.
- __Dynamic Route Patterns__ contain dynamic parts that can vary per request. The varying parts are named __subpatterns__ and are defined using either Perl-compatible regular expressions (PCRE) or by using __placeholders__

#### Static Route Patterns

A static route pattern is a regular string representing a URI. It will be compared directly against the `path` part of the current URL.

Examples:

-  `/about`
-  `/contact`

Usage Examples:

```php
// This route handling function will only be executed when visiting http(s)://www.example.org/about
$router->get('/about', function() {
    echo 'About Page Contents';
});
```

#### Dynamic PCRE-based Route Patterns

This type of Route Patterns contain dynamic parts which can vary per request. The varying parts are named __subpatterns__ and are defined using regular expressions.

Examples:

- `/movies/(\d+)`
- `/profile/(\w+)`

Commonly used PCRE-based subpatterns within Dynamic Route Patterns are:

- `\d+` = One or more digits (0-9)
- `\w+` = One or more word characters (a-z 0-9 _)
- `[a-z0-9_-]+` = One or more word characters (a-z 0-9 _) and the dash (-)
- `.*` = Any character (including `/`), zero or more
- `[^/]+` = Any character but `/`, one or more

Note: The [PHP PCRE Cheat Sheet](https://www.cs.washington.edu/education/courses/190m/12sp/cheat-sheets/php-regex-cheat-sheet.pdf) might come in handy.

The __subpatterns__ defined in Dynamic PCRE-based Route Patterns are converted to parameters which are passed into the route handling function. Prerequisite is that these subpatterns need to be defined as __parenthesized subpatterns__, which means that they should be wrapped between parens:

```php
// Bad
$router->get('/hello/\w+', function($name) {
    echo 'Hello ' . htmlentities($name);
});

// Good
$router->get('/hello/(\w+)', function($name) {
    echo 'Hello ' . htmlentities($name);
});
```

Note: The leading `/` at the very beginning of a route pattern is not mandatory, but is recommended.

When multiple subpatterns are defined, the resulting __route handling parameters__ are passed into the route handling function in the order they are defined in:

```php
$router->get('/movies/(\d+)/photos/(\d+)', function($movieId, $photoId) {
    echo 'Movie #' . $movieId . ', photo #' . $photoId;
});
```

#### Dynamic Placeholder-based Route Patterns

This type of Route Patterns are the same as __Dynamic PCRE-based Route Patterns__, but with one difference: they don't use regexes to do the pattern matching but they use the more easy __placeholders__ instead. Placeholders are strings surrounded by curly braces, e.g. `{name}`. You don't need to add parens around placeholders.

Examples:

- `/movies/{id}`
- `/profile/{username}`

Placeholders are easier to use than PRCEs, but offer you less control as they internally get translated to a PRCE that matches any character (`.*`).

```php
$router->get('/movies/{movieId}/photos/{photoId}', function($movieId, $photoId) {
    echo 'Movie #' . $movieId . ', photo #' . $photoId;
});
```

Note: the name of the placeholder does not need to match with the name of the parameter that is passed into the route handling function:

```php
$router->get('/movies/{foo}/photos/{bar}', function($movieId, $photoId) {
    echo 'Movie #' . $movieId . ', photo #' . $photoId;
});
```


### Optional Route Subpatterns

Route subpatterns can be made optional by making the subpatterns optional by adding a `?` after them. Think of blog URLs in the form of `/blog(/year)(/month)(/day)(/slug)`:

```php
$router->get(
    '/blog(/\d+)?(/\d+)?(/\d+)?(/[a-z0-9_-]+)?',
    function($year = null, $month = null, $day = null, $slug = null) {
        if (!$year) { echo 'Blog overview'; return; }
        if (!$month) { echo 'Blog year overview'; return; }
        if (!$day) { echo 'Blog month overview'; return; }
        if (!$slug) { echo 'Blog day overview'; return; }
        echo 'Blogpost ' . htmlentities($slug) . ' detail';
    }
);
```

The code snippet above responds to the URLs `/blog`, `/blog/year`, `/blog/year/month`, `/blog/year/month/day`, and `/blog/year/month/day/slug`.

Note: With optional parameters it is important that the leading `/` of the subpatterns is put inside the subpattern itself. Don't forget to set default values for the optional parameters.

The code snipped above unfortunately also responds to URLs like `/blog/foo` and states that the overview needs to be shown - which is incorrect. Optional subpatterns can be made successive by extending the parenthesized subpatterns so that they contain the other optional subpatterns: The pattern should resemble `/blog(/year(/month(/day(/slug))))` instead of the previous `/blog(/year)(/month)(/day)(/slug)`:

```php
$router->get('/blog(/\d+(/\d+(/\d+(/[a-z0-9_-]+)?)?)?)?', function($year = null, $month = null, $day = null, $slug = null) {
    // ...
});
```

Note: It is highly recommended to __always__ define successive optional parameters.

To make things complete use [quantifiers](http://www.php.net/manual/en/regexp.reference.repetition.php) to require the correct amount of numbers in the URL:

```php
$router->get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function($year = null, $month = null, $day = null, $slug = null) {
    // ...
});
```


### Subrouting / Mounting Routes

Use `$router->mount($baseroute, $fn)` to mount a collection of routes onto a subroute pattern. The subroute pattern is prefixed onto all following routes defined in the scope. e.g. Mounting a callback `$fn` onto `/movies` will prefix `/movies` onto all following routes.

```php
$router->mount('/movies', function() use ($router) {

    // will result in '/movies/'
    $router->get('/', function() {
        echo 'movies overview';
    });

    // will result in '/movies/id'
    $router->get('/(\d+)', function($id) {
        echo 'movie id ' . htmlentities($id);
    });

});
```

Nesting of subroutes is possible, just define a second `$router->mount()` in the callable that's already contained within a preceding `$router->mount()`.


### `Class@Method` calls

We can route to the class action like so:

```php
$router->get('/(\d+)', '\App\Controllers\User@showProfile');
```

When a request matches the specified route URI, the `showProfile` method on the `User` class will be executed. The defined route parameters will be passed to the class method.

The method can be static (recommended) or non-static (not-recommended). In case of a non-static method, a new instance of the class will be created.

If most/all of your handling classes are in one and the same namespace, you can set the default namespace to use on your router instance via `setNamespace()`

```php
$router->setNamespace('\App\Controllers');
$router->get('/users/(\d+)', 'User@showProfile');
$router->get('/cars/(\d+)', 'Car@showProfile');
```

### Custom 404

The default 404 handler sets a 404 status code and exits. You can override this default 404 handler by using `$router->set404(callable);`

```php
$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    // ... do something special here
});
```

Also supported are `Class@Method` callables:

```php
$router->set404('\App\Controllers\Error@notFound');
```

The 404 handler will be executed when no route pattern was matched to the current URL.

💡 You can also manually trigger the 404 handler by calling `$router->trigger404()`

```php
$router->get('/([a-z0-9-]+)', function($id) use ($router) {
    if (!Posts::exists($id)) {
        $router->trigger404();
        return;
    }

    // …
});
```


### Before Route Middlewares

`bramus/router` supports __Before Route Middlewares__, which are executed before the route handling is processed.

Like route handling functions, you hook a handling function to a combination of one or more HTTP request methods and a specific route pattern.

```php
$router->before('GET|POST', '/admin/.*', function() {
    if (!isset($_SESSION['user'])) {
        header('location: /auth/login');
        exit();
    }
});
```

Unlike route handling functions, more than one before route middleware is executed when more than one route match is found.


### Before Router Middlewares

Before route middlewares are route specific. Using a general route pattern (viz. _all URLs_), they can become __Before Router Middlewares__ _(in other projects sometimes referred to as before app middlewares)_ which are always executed, no matter what the requested URL is.

```php
$router->before('GET', '/.*', function() {
    // ... this will always be executed
});
```


### After Router Middleware / Run Callback

Run one (1) middleware function, name the __After Router Middleware__ _(in other projects sometimes referred to as after app middlewares)_ after the routing was processed. Just pass it along the `$router->run()` function. The run callback is route independent.

```php
$router->run(function() { … });
```

Note: If the route handling function has `exit()`ed the run callback won't be run.


### Overriding the request method

Use `X-HTTP-Method-Override` to override the HTTP Request Method. Only works when the original Request Method is `POST`. Allowed values for `X-HTTP-Method-Override` are `PUT`, `DELETE`, or `PATCH`.


### Subfolder support

Out-of-the box `bramus/router` will run in any (sub)folder you place it into … no adjustments to your code are needed. You can freely move your _entry script_ `index.php` around, and the router will automatically adapt itself to work relatively from the current folder's path by mounting all routes onto that __basePath__.

Say you have a server hosting the domain `www.example.org` using `public_html/` as its document root, with this little _entry script_ `index.php`:

```php
$router->get('/', function() { echo 'Index'; });
$router->get('/hello', function() { echo 'Hello!'; });
```

- If your were to place this file _(along with its accompanying `.htaccess` file or the like)_ at the document root level (e.g. `public_html/index.php`), `bramus/router` will mount all routes onto the domain root (e.g. `/`) and thus respond to `https://www.example.org/` and `https://www.example.org/hello`.

- If you were to move this file _(along with its accompanying `.htaccess` file or the like)_ into a subfolder (e.g. `public_html/demo/index.php`), `bramus/router` will mount all routes onto the current path (e.g. `/demo`) and thus repsond to `https://www.example.org/demo` and `https://www.example.org/demo/hello`. There's **no** need for `$router->mount(…)` in this case.

#### Disabling subfolder support

In case you **don't** want `bramus/router` to automatically adapt itself to the folder its being placed in, it's possible to manually override the _basePath_ by calling `setBasePath()`. This is necessary in the _(uncommon)_ situation where your _entry script_ and your _entry URLs_ are not tightly coupled _(e.g. when the entry script is placed into a subfolder that does not need be part of the URLs it responds to)_.

```php
// Override auto base path detection
$router->setBasePath('/');

$router->get('/', function() { echo 'Index'; });
$router->get('/hello', function() { echo 'Hello!'; });

$router->run();
```

If you were to place this file into a subfolder (e.g. `public_html/some/sub/folder/index.php`), it will still mount the routes onto the domain root (e.g. `/`) and thus respond to `https://www.example.org/` and `https://www.example.org/hello` _(given that your `.htaccess` file – placed at the document root level – rewrites requests to it)_

## Integration with other libraries

Integrate other libraries with `bramus/router` by making good use of the `use` keyword to pass dependencies into the handling functions.

```php
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
```

Given this structure it is still possible to manipulate the output from within the After Router Middleware


## A note on working with PUT

There's no such thing as `$_PUT` in PHP. One must fake it:

```php
$router->put('/movies/(\d+)', function($id) {

    // Fake $_PUT
    $_PUT  = array();
    parse_str(file_get_contents('php://input'), $_PUT);

    // ...

});
```


## A note on making HEAD requests

When making `HEAD` requests all output will be buffered to prevent any content trickling into the response body, as defined in [RFC2616 (Hypertext Transfer Protocol -- HTTP/1.1)](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4):

> The HEAD method is identical to GET except that the server MUST NOT return a message-body in the response. The metainformation contained in the HTTP headers in response to a HEAD request SHOULD be identical to the information sent in response to a GET request. This method can be used for obtaining metainformation about the entity implied by the request without transferring the entity-body itself. This method is often used for testing hypertext links for validity, accessibility, and recent modification.

To achieve this, `bramus/router` but will internally re-route `HEAD` requests to their equivalent `GET` request and automatically suppress all output.


## Unit Testing & Code Coverage

`bramus/router` ships with unit tests using [PHPUnit](https://github.com/sebastianbergmann/phpunit/).

- If PHPUnit is installed globally run `phpunit` to run the tests.

- If PHPUnit is not installed globally, install it locally throuh composer by running `composer install --dev`. Run the tests themselves by calling `vendor/bin/phpunit`.

  The included `composer.json` will also install `php-code-coverage` which allows one to generate a __Code Coverage Report__. Run `phpunit --coverage-html ./tests-report` (XDebug required), a report will be placed into the `tests-report` subfolder.


## Acknowledgements

`bramus/router` is inspired upon [Klein](https://github.com/chriso/klein.php), [Ham](https://github.com/radiosilence/Ham), and [JREAM/route](https://bitbucket.org/JREAM/route) . Whilst Klein provides lots of features it is not object oriented. Whilst Ham is Object Oriented, it's bad at _separation of concerns_ as it also provides templating within the routing class. Whilst JREAM/route is a good starting point it is limited in what it does (only GET routes for example).



## License

`bramus/router` is released under the MIT public license. See the enclosed `LICENSE` for details.
