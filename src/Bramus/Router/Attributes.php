<?php

namespace Bramus\Router;

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
     * @var object|callable The function to be executed when no route has been matched
     */
    public $notFoundCallback;
}
