<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Example
{

    /**
     * @return Response with plain text
     */
    public function action1()
    {

        // Create a Response object with a simple text content
        $content = 'Hello, this is a plain text example from / or /action1 routes!';
        $response = new Response($content);

        // Customize the response, if needed
        $response->headers->set('Content-Type', 'text/plain');
        $response->setStatusCode(Response::HTTP_OK);

        return $response->send();
    }

    /**
     * @return JsonResponse with json
     */
    public function action2()
    {
        // Create an associative array representing the data to be encoded as JSON
        $data = [
            'message' => 'Hello, this is a JSON response example from /api/v1/action2 route!',
            'status' => 'success',
        ];

        // Use JsonResponse to create a JSON response with the provided data
        $response = new JsonResponse($data);

        // Optionally, you can customize the response, e.g., set additional headers

        return $response->send();
    }

    /**
     * @return Response with plain text for no matching route found!
     */
    public function notFoundAction()
    {
        // Create a plain text Response for no matching route found
        $response = new Response('404 Not Found - No matching route found!');

        // Set the status code to 404
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        // Optionally, you can customize other properties of the response
        $response->headers->set('Content-Type', 'text/plain');

        return $response->send();
    }

}
