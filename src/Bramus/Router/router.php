<?php
/**
 * @author		Bram(us) Van Damme <bramus@bram.us>
 * @copyright	Copyright (c), 2013 Bram(us) Van Damme
 * @license		MIT public license
 */

namespace Bramus\Router;

class Router {


	/**
	 * @var array The route patterns
	 */
	private $routePatterns = array();


	/**
	 * @var array The route handling functions
	 */
	private $routeHandlers = array();


	/**
	 * @var array The before middleware route patterns
	 */
	private $beforePatterns = array();


	/**
	 * @var array The before middleware handling functions
	 */
	private $beforeHandlers = array();


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
			$this->beforePatterns[$method][] = $pattern;
			$this->beforeHandlers[$method][] = $fn;
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
			$this->routePatterns[$method][] = $pattern;
			$this->routeHandlers[$method][] = $fn;
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
		if (isset($this->beforePatterns[$_SERVER['REQUEST_METHOD']]))
			$this->handle($this->beforePatterns[$_SERVER['REQUEST_METHOD']], $this->beforeHandlers);

		// Handle all routes
		$numHandled = 0;
		if (isset($this->routePatterns[$_SERVER['REQUEST_METHOD']]))
			$numHandled = $this->handle($this->routePatterns[$_SERVER['REQUEST_METHOD']], $this->routeHandlers, true);

		// If no route was handled, trigger the 404 (if any)
		if ($numHandled == 0) {
			if ($this->notFound && is_callable($this->notFound)) call_user_func($this->notFound);
			else header('HTTP/1.1 404 Not Found');
		}
		// If a route was handled, perform the finish callback (if any)
		else {
			if ($callback) $callback();
		}
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
	 * @param array $patterns Collection of route patterns
	 * @param array $handlers Collection of handling functions
	 * @param boolean $quitAfterRun Does the handle function need to quit after one route was matched?
	 * @return int The number of routes handled
	 */
	private function handle($patterns, $handlers, $quitAfterRun = false) {

		// Counter to keep track of the number of routes we've handled
		$numHandled = 0;

		// The current page URL
		$uri = $_SERVER['REQUEST_URI'];

		// Remove rewrite basepath (= allows one to run the router in a subfolder)
		$basepath = implode('/', array_slice(explode('/', $_SERVER["SCRIPT_NAME"]), 0, -1)) . '/';
		$uri = substr($uri, strlen($basepath));

		// Don't take query params into account on the URL
		if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));

		// Remove trailing slash + enforce a slash at the start
		$uri = '/' . trim($uri, '/');

		// Variables in the URL
		$urlvars = array();

		// Loop all routes
		foreach ($patterns as $idx => $pattern) {

			// we have a match!
			if (preg_match_all("#^$pattern$#", $uri, $matches, PREG_SET_ORDER)) {

				// Extract the matched URL parameters (and only the parameters)
				$params = array_map(function($match) {
					$var = explode('/', trim($match, '/'));
					return isset($var[0]) ? $var[0] : null;
				}, array_slice($matches[0], 1));

				// call the handling function with the URL parameters
				call_user_func_array($handlers[$_SERVER['REQUEST_METHOD']][$idx], $params);

				// yay!
				$numHandled++;

				// If we need to quit, then quit
				if ($quitAfterRun) break;

			}

		}

		// Return the number of routes handled
		return $numHandled;

	}

}

// EOF