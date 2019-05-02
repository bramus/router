<?php

namespace Bramus\Router;

/**
 * Storage router configuration
 * @package Bramus\Router
 */
class Attributes
{
    /**
     * @var array The route patterns and their handling functions
     */
    public $afterRoutes = array();
    /**
     * @var array The before middleware route patterns and their handling functions
     */
    public $beforeRoutes = array();
    /**
     * @var array The 404 page handling functions
     */
    public $notFoundCallback = array();

    /**
     * Add a route
     * @param $group string afterRoutes|beforeRoutes|notFoundCallback
     * @param $method string Http method
     * @param array $route A route info
     */
    public function addRoute($group, $method, array $route)
    {
        if ($route['domain']) {
            if (!isset($this->{$group}[$method])) {
                $this->{$group}[$method] = array();
            }
            array_unshift($this->{$group}[$method], $route);
        } else {
            $this->{$group}[$method][] = $route;
        }
    }
}
