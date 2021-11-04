<?php

namespace Halo;

class Request {
    
    /**
     * Request method
     * 
     * @since   2.0
     * @var     string
     */
    protected $method = null;

    /**
     * Request uri
     * 
     * @since   2.0
     * @var     string
     */
    protected $uri = null;

    /**
     * Request headers
     * 
     * @since   2.0
     * @var     array
     */
    protected $headers = array();

    /**
     * Request body
     * 
     * @since   2.0
     * @var     mixed
     */
    protected $body = null;

    /**
     * Attributes
     * 
     * @since   2.0
     * @var     array
     */
    protected $attributes = array();

    /**
     * Halo Request constructor
     * 
     * @since   2.0
     * @param   string  request method
     * @param   string  reqyest uri
     * @param   array   request headers
     * @param   mixed   request body
     * @param   array   attributes
     * @return  void
     */
    public function __construct(
        $method = null,
        $uri = null,
        $headers = array(),
        $body = null,
        $attributes = array()
    ) {
        $this->method       = $method;
        $this->uri          = $uri;
        $this->headers      = $headers;
        $this->body         = $body;
        $this->attributes   = $attributes;
    }

    /**
     * Get request method
     * 
     * @since   2.0
     * @return  string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Get current request uri
     * 
     * @since   2.0
     * @return  string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Get request headers
     * 
     * @since   2.0
     * @return  array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Get single header
     * 
     * @since   2.0
     * @param   string  header key
     * @return  string|null header value
     */
    public function getHeader(
        $key
    ) {
        return $this->headers[$key] ?? null;
    }

    /**
     * Checks if request has header or not
     * 
     * @since   2.0
     * @param   string  header key
     * @return  bool
     */
    public function hasHeader(
        $key
    ) {
        return isset( $this->headers );
    }

    /**
     * Returns request body
     * 
     * @since   2.0
     * @return  mixed   request body
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Returns request body as json
     * 
     * @since   2.0
     * @param   bool    associative
     * @return  array|null   parsed json
     */
    public function getJsonBody(
        $associative = true
    ) {

        $content = file_get_contents('php://input');

        if ( !is_string( $content ) )
            return;

        /**
         * Maybe in future we should only return php://input when content type is json
         * 
         * @todo
         */
        $content = json_decode($content, $associative);

        if ( json_last_error() === JSON_ERROR_NONE )
            return $content;
    }

    /**
     * Set a custom attribute
     * 
     * @since   2.0
     * @param   string  attribute key
     * @param   mixed   attribute value
     * @return  void
     */
    public function withAttribute(
        $key,
        $value
    ) {
        $this->attributes[ $key ] = $value;
    }

    /**
     * Get attribute in request
     * 
     * @since   2.0
     * @param   string  attribute key
     * @return  mixed|null  attribute value or null if not founded
     */
    public function getAttribute(
        $key
    ) {
        return $this->attributes[ $key ] ?? null;
    }

}
