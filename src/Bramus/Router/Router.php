<?php

/**
 * @author      Bram(us) Van Damme <bramus@bram.us>
 * @copyright   Copyright (C), 2013 Bram(us) Van Damme
 * @license     MIT public license
 */

namespace Bramus\Router;

/**
 * Class Router
 * @package Bramus\Router
 */
final class Router
{
    /**
     * @var array The route patterns and their handling functions
     */
    private $routes = array();

    /**
     * @var array The before middleware route patterns and their handling functions
     */
    private $beforeRoutes = array();

    /**
     * @var object|callable The function to be executed when no route has been matched
     */
    protected $notFound;

    /**
     * @var string Current base route, used for (sub)route mounting
     */
    private $baseRoute = '';

    /**
     * @var string The Request Method that needs to be handled
     */
    private $requestMethod = '';

    /**
     * Store a before middleware route and a handling function to be executed when accessed using one of the specified methods
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function before($methods, $pattern, $fn)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;

        foreach(explode('|', $methods) as $method) {
            $this->beforeRoutes[$method][] = array(
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
     * @param object|callable $fn The handling function to be executed
     */
    public function match($methods, $pattern, $fn)
    {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;

        foreach(explode('|', $methods) as $method) {
            $this->routes[$method][] = array(
                'pattern' => $pattern,
                'fn' => $fn
            );
        }
    }

    /**
     * Shorthand for a route accessed using any method
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function all($pattern, $fn)
    {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using GET
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function get($pattern, $fn)
    {
        $this->match('GET', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using POST
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function post($pattern, $fn)
    {
        $this->match('POST', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PATCH
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function patch($pattern, $fn)
    {
        $this->match('PATCH', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using DELETE
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function delete($pattern, $fn)
    {
        $this->match('DELETE', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PUT
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function put($pattern, $fn)
    {
        $this->match('PUT', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using OPTIONS
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $fn The handling function to be executed
     */
    public function options($pattern, $fn)
    {
        $this->match('OPTIONS', $pattern, $fn);
    }

    /**
     * Mounts a collection of callable's onto a base route
     *
     * @param string $baseRoute The route sub pattern to mount the callable's on
     * @param callable $fn The method to be called
     */
    public function mount($baseRoute, $fn)
    {
        // track current base route
        $currentBaseRoute = $this->baseRoute;

        // build new base route string
        $this->baseRoute .= $baseRoute;

        // call the desired method
        call_user_func($fn);

        // restore original base route
        $this->baseRoute = $currentBaseRoute;
    }

    /**
     * Get all request headers
     *
     * @return array The request headers
     */
    public function getRequestHeaders()
    {
        // check if php method getallheaders is built in current php distribution
        if(function_exists('getallheaders')) {
            return getallheaders();
        }

        // getallheaders not available: manually extract 'm
        $headers = array();

        foreach($_SERVER as $name => $value) {
            if((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get the request method used, taking overrides into account
     *
     * @return string The Request method to handle
     */
    public function getRequestMethod()
    {
        // get requested method present from $_SERVER super global variable
        $method = $_SERVER['REQUEST_METHOD'];

        // if it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        } // if it's a POST request, check for a method override header
        elseif($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if(isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }

    /**
     * Execute the router: Loop all defined before middleware's and routes, and execute the handling function if a match was found
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
     * @return bool
     */
    public function run($callback = null)
    {
        // define which method we need to handle
        $this->requestMethod = $this->getRequestMethod();

        // handle all before the middleware's
        if(isset($this->beforeRoutes[$this->requestMethod])) {
            $this->handle($this->beforeRoutes[$this->requestMethod]);
        }

        // handle all routes
        $handledCount = 0;
        if(isset($this->routes[$this->requestMethod])) {
            $handledCount = $this->handle($this->routes[$this->requestMethod], true);
        }

        // if no route was handled, trigger the 404 (if any)
        if($handledCount === 0) {
            if($this->notFound && is_callable($this->notFound)) {
                call_user_func($this->notFound);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            }
        } // if a route was handled, perform the finish callback (if any)
        elseif($callback) {
            $callback();
        }

        // if it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        // return true if a route was handled, false otherwise
        if($handledCount === 0) {
            return false;
        }

        return true;
    }

    /**
     * Set the 404 handling function
     *
     * @param object|callable $fn The function to be executed
     */
    public function set404($fn)
    {
        $this->notFound = $fn;
    }

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function
     *
     * @param array $routes Collection of route patterns and their handling functions
     * @param boolean $quitAfterRun Does the handle function need to quit after one route was matched?
     * @return int The number of routes handled
     */
    private function handle($routes, $quitAfterRun = false)
    {
        // counter to keep track of the number of routes we've handled
        $numHandled = 0;

        // the current page URL
        $uri = $this->getCurrentUri();

        // loop all routes
        foreach($routes as $route) {
            // check if we have a match!
            if(preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
                // Rework matches to only contain the matches, not the orig string
                $matches = array_slice($matches, 1);
                // Extract the matched URL parameters (and only the parameters)
                $params = array_map(function($match, $index) use ($matches) {
                    // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                    if(isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    } // We have no following parameters: return the whole lot
                    else {
                        return (isset($match[0][0]) ? trim($match[0][0], '/') : null);
                    }
                }, $matches, array_keys($matches));

                // call the handling function with the URL parameters if the desired input is callable
                if(is_callable($route['fn'])) {
                    call_user_func_array($route['fn'], $params);
                } // if not, check the existence of special parameters
                elseif(stripos($route['fn'], '@') !== false) {
                    // explode segments of given route
                    $segments = explode('@', $route['fn']);
                    // check if class exists, if not just ignore.
                    // @todo create check for namespace binding. because this way will only works if or the entire namespace is given or the class doesn't be part of a namespace, in other way the user will need to a pre-check.
                    if(class_exists($segments[0])) {
                        // first check if is a static method, directly trying to invoke it. if isn't a valid static method, we will try as a normal method invocation.
                        if(forward_static_call_array(array($segments[0], $segments[1]), $params) === false) {
                            // try call the method as an non-static method.
                            call_user_func_array(array(new $segments[0], $segments[1]), $params);
                        }
                    }
                }

                $numHandled++;

                // if we need to quit, then quit
                if($quitAfterRun) {
                    break;
                }
            }
        }

        // return the number of routes handled
        return $numHandled;
    }

    /**
     * Define the current relative URI
     *
     * @return string
     */
    protected function getCurrentUri()
    {
        // get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';

        $uri = substr($_SERVER['REQUEST_URI'], strlen($basePath));

        // don't take query params into account on the URL
        if(strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // remove trailing slash + enforce a slash at the start
        return '/' . trim($uri, '/');
    }
}
