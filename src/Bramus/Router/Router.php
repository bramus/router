<?php
/**
 * @author		Bram(us) Van Damme <bramus@bram.us>
 * @copyright	Copyright (c), 2013 Bram(us) Van Damme
 * @license		MIT public license
 */

namespace Bramus\Router;

class Router {


	/**
	 * @var array The route patterns and their handling functions
	 */
	private $routes = array();


	/**
	 * @var array The before middleware route patterns and their handling functions
	 */
	private $befores = array();


	/**
	 * @var object The function to be executed when no route has been matched
	 */
	private $notFound;


	/**
	 * Store a before middleware route and a handling function to be executed when accessed using one of the specified methods
	 *
	 * @param string $methods Allowed methods, | delimited
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function before($methods, $pattern, $fn) {

		$pattern = '/' . trim($pattern, '/');

		foreach (explode('|', $methods) as $method) {
			$this->befores[$method][] = array(
				'pattern' => $pattern,
				'fn' => $fn
			);
		}

	}

	/**
	 * Store a route and a handling function to be executed when accessed using one of the specified methods
	 *
	 * @param string $methods Allowed methods, | delimited
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function match($methods, $pattern, $fn) {

		$pattern = '/' . trim($pattern, '/');

		foreach (explode('|', $methods) as $method) {
			$this->routes[$method][] = array(
				'pattern' => $pattern,
				'fn' => $fn
			);
		}

	}


	/**
	 * Shorthand for a route accessed using GET
	 *
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function get($pattern, $fn) {
		$this->match('GET', $pattern, $fn);
	}


	/**
	 * Shorthand for a route accessed using POST
	 *
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function post($pattern, $fn) {
		$this->match('POST', $pattern, $fn);
	}


	/**
	 * Shorthand for a route accessed using DELETE
	 *
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function delete($pattern, $fn) {
		$this->match('DELETE', $pattern, $fn);
	}


	/**
	 * Shorthand for a route accessed using PUT
	 *
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function put($pattern, $fn) {
		$this->match('PUT', $pattern, $fn);
	}


	/**
	 * Shorthand for a route accessed using OPTIONS
	 *
	 * @param string $pattern A route pattern such as /about/system
	 * @param object $fn The handling function to be executed
	 */
	public function options($pattern, $fn) {
		$this->match('OPTIONS', $pattern, $fn);
	}


	/**
	 * Execute the router: Loop all defined before middlewares and routes, and execute the handling function if a mactch was found
	 *
	 * @param object $callback Function to be executed after a matching route was handled (= after router middleware)
	 */
	public function run($callback = null) {

		// Handle all before middlewares
		if (isset($this->befores[$_SERVER['REQUEST_METHOD']]))
			$this->handle($this->befores[$_SERVER['REQUEST_METHOD']]);

		// Handle all routes
		$numHandled = 0;
		$result = null;
		if (isset($this->routes[$_SERVER['REQUEST_METHOD']]))
			list($numHandled, $result) = $this->handle($this->routes[$_SERVER['REQUEST_METHOD']], true);

		// If no route was handled, trigger the 404 (if any)
		if ($numHandled == 0) {
			if ($this->notFound && is_callable($this->notFound)) call_user_func($this->notFound);
			else header('HTTP/1.1 404 Not Found');
		}
		// If a route was handled, perform the finish callback (if any)
		else {
			if ($callback) $callback();
		}
		
		return $result;
	}


	/**
	 * Set the 404 handling function
	 * @param object $fn The function to be executed
	 */
	public function set404($fn) {
		$this->notFound = $fn;
	}


	/**
	 * Handle a a set of routes: if a match is found, execute the relating handling function
	 * @param array $routes Collection of route patterns and their handling functions
	 * @param boolean $quitAfterRun Does the handle function need to quit after one route was matched?
	 * @return mixed An associate array holding the number of routes handled as well as the result returned by the handler
	 */
	private function handle($routes, $quitAfterRun = false) {

		// Counter to keep track of the number of routes we've handled
		$numHandled = 0;

		// The current page URL
		$uri = $this->getCurrentUri();

		// Variables in the URL
		$urlvars = array();

		// Loop all routes
		foreach ($routes as $route) {

			// we have a match!
			if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_SET_ORDER)) {

				// Extract the matched URL parameters (and only the parameters)
				$params = array_map(function($match) {
					$var = explode('/', trim($match, '/'));
					return isset($var[0]) ? $var[0] : null;
				}, array_slice($matches[0], 1));

				// call the handling function with the URL parameters
				$response = call_user_func_array($route['fn'], $params);

				// yay!
				$numHandled++;

				// If we need to quit, then quit
				// Return response, only and only if it was asked to quit on first match
				if ($quitAfterRun) return array($numHandled, $response);

			}

		}

		// Return the number of routes handled with no response
		return array($numHandled, false);

	}


	/**
	 * Define the current relative URI
	 * @return string
	 */
	private function getCurrentUri() {

		// Current Request URI
		$uri = $_SERVER['REQUEST_URI'];

		// Remove rewrite basepath (= allows one to run the router in a subfolder)
		$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
		$uri = substr($uri, strlen($basepath));

		// Don't take query params into account on the URL
		if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));

		// Remove trailing slash + enforce a slash at the start
		$uri = '/' . trim($uri, '/');

		return $uri;

	}

}

// EOF
