<?php

namespace Halo;

use Halo\Request;

class Router extends \Bramus\Router\Router {
    
    public function invoke($fn, $params = array())
    {

        if (is_callable($fn)) {
            
            $request = new Request(
                $this->getRequestMethod(),
                $this->getCurrentUri(),
                $this->getRequestHeaders(),
                $this->getRequestBody()
            );

            $params[] = $request;

            call_user_func_array($fn, $params);
        }

        // If not, check the existence of special parameters
        elseif (stripos($fn, '@') !== false) {
            // Explode segments of given route
            list($controller, $method) = explode('@', $fn);

            // Adjust controller class if namespace has been set
            if ($this->getNamespace() !== '') {
                $controller = $this->getNamespace() . '\\' . $controller;
            }

            try {
                $reflectedMethod = new \ReflectionMethod($controller, $method);
                // Make sure it's callable
                if ($reflectedMethod->isPublic() && (!$reflectedMethod->isAbstract())) {
                    if ($reflectedMethod->isStatic()) {
                        forward_static_call_array(array($controller, $method), $params);
                    } else {
                        // Make sure we have an instance, because a non-static method must not be called statically
                        if (\is_string($controller)) {
                            $controller = new $controller();
                        }
                        call_user_func_array(array($controller, $method), $params);
                    }
                }
            } catch (\ReflectionException $reflectionException) {
                // The controller class is not available or the class does not have the method $method
            }
        }
    }

    /**
     * Get request body
     * 
     * @since   2.0
     * @return  mixed
     */
    public function getRequestBody() {
        if ( ! empty( $_POST) ) {
            return $_POST;
        }else {
            return file_get_contents('php://input');
        }
    }
}
