<?php

namespace App;

class Router
{
    protected $router;
    public function __construct()
    {
        // Create Router instance
        $this->router = new \Bramus\Router\Router();   
        $this->router->setNamespace('\App\Controller');
    }

    /**
     * manage all your routes here
     * Route example 
     *  
     *  http://localhost:8080
     *  http://localhost:8080/action1
     * 
     *  http://localhost:8080/api/v1/action2
     *  
     *  http://localhost:8080/no-action-found 
     */
    public function routes()
    {
        ////////////Declare the routes here//////////////
        
        //Use a shorter local variable name
        $rout = &$this->router;

        
        //without prefix
        $rout->get('/', 'Example@action1');
        $rout->get('/action1', 'Example@action1');

        //route with prefix
        $rout->mount('/api/v1', function () use ($rout) {
             $rout->get('action2', 'Example@action2');
        });

        //if nothing matches
          $rout->set404('/api(/.*)?', 'Example@notFoundAction');

        //finally return the route
        return $rout;
    }
}
