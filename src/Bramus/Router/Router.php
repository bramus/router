<?php

/**
 * @author      Bram(us) Van Damme <bramus@bram.us>
 * @copyright   Copyright (c), 2013 Bram(us) Van Damme
 * @license     MIT public license
 */

namespace Bramus\Router;
use \ErrorException;

/**
 * Router Class
 */
class Router
{
    /**
     * @var array The route patterns and their handling functions
     */
    private $afterRoutes = [];

    /**
     * @var array The before middleware route patterns and their handling functions
     */
    private $beforeRoutes = [];

    /**
     * @var object|callable The function to be executed when no route has been matched
     */
    protected $notFoundCallback;

    /**
     * @var string Current base route, used for (sub)route mounting
     */
    private $baseRoute = '';

    /**
     * @var string The Request Method that needs to be handled
     */
    private $requestedMethod = '';

    /**
     * @var string The Server Base Path for Router Execution
     */
    private $serverBasePath = '/';

    /**
     * @var string Default Controllers Namespace
     */
    private $namespace = '';

    /**
     * Parse a pattern
     *
     * @param string $pattern A route pattern such as /about/system
     * @return string
     */
    private function parsePattern(String $pattern){
        $pattern = $this->baseRoute.'/'.trim($pattern, '/');
        return $this->baseRoute ? rtrim($pattern, '/') : $pattern;
    }

    /**
     * Store a before middleware route and a handling function 
     * to be executed when accessed using one of the specified methods.
     *
     * @param string          $methods Allowed methods, | delimited
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function before(String $methods, String $pattern, $fn)
    {
        $pattern = $this->parsePattern($pattern);

        foreach (explode('|', $methods) as $method) {
            $this->beforeRoutes[$method][] = array(
                'pattern' => $pattern,
                'fn' => $fn,
            );
        }
    }

    /**
     * Store a route and a handling function to be executed when
     * accessed using one of the specified methods.
     *
     * @param string          $methods Allowed methods, | delimited
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function match(String $methods, String $pattern, $fn)
    {
        $pattern = $this->parsePattern($pattern);

        foreach (explode('|', $methods) as $method) {
            $this->afterRoutes[$method][] = array(
                'pattern' => $pattern,
                'fn' => $fn,
            );
        }
    }

    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function all(String $pattern, $fn)
    {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function get(String $pattern, $fn)
    {
        $this->match('GET', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function post(String $pattern, $fn)
    {
        $this->match('POST', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PATCH.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function patch(String $pattern, $fn)
    {
        $this->match('PATCH', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using DELETE.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function delete(String $pattern, $fn)
    {
        $this->match('DELETE', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PUT.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function put(String $pattern, $fn)
    {
        $this->match('PUT', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using OPTIONS.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function options(String $pattern, $fn)
    {
        $this->match('OPTIONS', $pattern, $fn);
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string   $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $fn        The callback method
     */
    public function mount(String $baseRoute, $fn)
    {
        # Copy class base route
        $classBaseRoute = $this->baseRoute;
        # Temporarily change the class base route
        $this->baseRoute .= $baseRoute;
        # Execute function
        call_user_func($fn);
        # Restore class base route
        $this->baseRoute = $classBaseRoute;
    }

    /**
     * Get all request headers.
     *
     * @return array The request headers
     */
    public function getRequestHeaders()
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                return $headers;
            }
        }

        foreach ($_SERVER as $name => $value) {
            if (
                (substr($name, 0, 5) == 'HTTP_') 
                || 
                ($name == 'CONTENT_TYPE') 
                || 
                ($name == 'CONTENT_LENGTH')
            ) {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get the request method used, taking overrides into account.
     *
     * @return string The Request method to handle
     */
    public function getRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        /**
         * Override HEAD request to GET and prevent any output.
         * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
         */
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            return 'GET';
        }

        /**
         * Check for method override header on POST request
         */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (
                isset($headers['X-HTTP-Method-Override']) 
                && 
                in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])
            ) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }

    /**
     * Set a Default Lookup Namespace for Callable methods.
     *
     * @param string $namespace A given namespace
     */
    public function setNamespace(String $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Get the given Namespace before.
     *
     * @return string The given Namespace if exists
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Run the router
     * Will loop thru all defined before middleware's and routes and execute
     * handling function if a match was found.
     *
     * @param object|callable $callback Function to execute after a matching route was handled
     *
     * @return bool
     */
    public function run($callback = null)
    {
        # Define which method we need to handle
        $this->requestedMethod = $this->getRequestMethod();

        # Handle all before middlewares
        if (isset($this->beforeRoutes[$this->requestedMethod])) {
            $this->handle($this->beforeRoutes[$this->requestedMethod]);
        }

        # Handle all routes
        $routesHandled = 0;
        if (isset($this->afterRoutes[$this->requestedMethod])) {
            $routesHandled = $this->handle($this->afterRoutes[$this->requestedMethod], true);
        }

        # If no route was handled, trigger 404 callback (if any)
        if ($routesHandled === 0) {
            if ($this->notFoundCallback) {
                $this->invoke($this->notFoundCallback);
            } else {
                header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            }
        } # If a route was handled, perform the finish callback (if any)
        else {
            if ($callback && is_callable($callback)) {
                $callback();
            }
        }

        # If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        # Return true if a route was handled
        return $routesHandled !== 0;
    }

    /**
     * Set the 404 handling function.
     *
     * @param object|callable $fn The function to be executed
     */
    public function set404($fn)
    {
        $this->notFoundCallback = $fn;
    }

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes       Collection of route patterns and their handling functions
     * @param bool  $quitAfterMatch Does the handle function need to quit after one route was matched?
     *
     * @return int The number of routes handled
     */
    private function handle($routes, $quitAfterMatch = false)
    {
        $routesHandled = 0;
        $uri = $this->getCurrentUri();

        foreach ($routes as $route) {
            # Replace all curly braces matches {} into RegEx patterns
            $route['pattern'] = preg_replace('/\/{(.*?)}/', '/(.*?)', $route['pattern']);

            # No match was found
            if(!preg_match_all('#^'.$route['pattern'].'$#', $uri, $matches, PREG_OFFSET_CAPTURE)){
                continue;
            }

            # Rework matches to only contain the matches, not the original string
            $matches = array_slice($matches, 1);

            # Extract the matched URL parameters (and only the parameters)
            $params = array_map(function ($match, $index) use ($matches) {

                # We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                    return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                } # We have no following parameters: return the whole lot
                else {
                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                }
            }, $matches, array_keys($matches));

            # Call the handling function with the URL parameters if the desired input is callable
            $this->invoke($route['fn'], $params);

            ++$routesHandled;

            # Quit after a match was found
            if ($quitAfterMatch) {
                break;
            }
        }

        return $routesHandled;
    }

    /**
     * Invoke a function/object
     *
     * @param callable|object|string $fn     The function to invoke
     * @param array                  $params The parameters to pass
     *
     * @return bool true if something was invoked
     */
    private function invoke($fn, $params = []) {

        # Call the function if it's callable
        if (is_callable($fn)) {
            call_user_func_array($fn, $params);
            return true;
        } 

        # Check if we need to call a method
        if (stripos($fn, '@') !== false) {

            # Explode segments of given route
            list($controller, $method) = explode('@', $fn);

            # Adjust namespace
            if ($this->getNamespace() !== '') {
                $controller = $this->getNamespace().'\\'.$controller;
            }

            # Check if class exsist
            if(!class_exists($controller)){
                return false;
            }

            # Call method
            set_error_handler(function($level, $msg, $file, $line, Array $context){
                if (0 === error_reporting()) {
                    // Error suppressed
                    return false;
                }
                if(substr($msg, 0, 65) === 'call_user_func_array() expects parameter 1 to be a valid callback'){
                    throw new \ErrorException("Bramus\Router: class '{$context['controller']}' does not have a method '{$context['method']}'", 0, $level, $file, $line);
                }
            }, E_WARNING);
            call_user_func_array([new $controller(), $method], $params);
            restore_error_handler();

            return true;
        }else{
            # Nothing to invoke!
            return false;
        }
    }

    /**
     * Remove query parameters and rewrite base from Request URI and return it
     *
     * @return string
     */
    protected function getCurrentUri()
    {
        # Remove rewrite base path from current Request URI
        $uri = substr($_SERVER['REQUEST_URI'], strlen($this->getBasePath()));

        # Remove query parameters
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        # Remove trailing slash and enforce a slash at the start
        return '/'.trim($uri, '/');
    }

    /**
     * Return server base path
     *
     * @return string
     */
    protected function getBasePath()
    {   
        return $this->serverBasePath;
    }

    /**
     * Set base path
     *
     * @param string $path The path
     *
     * @return void
     */
    public function setBasePath(String $path){
        $this->serverBasePath = $path;
    }
}
