<?php

    // In case one is using PHP 5.4+'s built-in server
    $filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (php_sapi_name() === 'cli-server' && is_file($filename)) {
        return false;
    }

    // Include the Router class
    // @note: it's recommended to just use the composer autoloader when working with other packages too
    require_once __DIR__ . '/../src/Bramus/Router/Router.php';

    /**
     * A Multilingual Router
     */
    class MultilangRouter extends \Bramus\Router\Router
    {
        /**
         * The Default langauge
         * @var string
         */
        private $defaultLanguage;

        /**
         * List of allowed languages
         * @var array
         */
        private $allowedLanguages = [];

        /**
         * A Multilingual Router
         * @param array  $allowedLanguages
         * @param string $defaultLanguage
         */
        public function __construct(array $allowedLanguages, $defaultLanguage)
        {

            // Store passed in data
            $this->allowedLanguages = $allowedLanguages;
            $this->defaultLanguage = (in_array($defaultLanguage, $allowedLanguages) ? $defaultLanguage : $allowedLanguages[0]);

            // Visiting the root? Redirect to the default language index
            $this->match('GET|POST|PUT|DELETE|HEAD', '/', function () {
                header('location: /' . $this->defaultLanguage);
                exit();
            });

            // Create a before handler to make sure the language checks out when visiting anything but the root.
            // If the language doesn't check out, redirect to the default language index
            $this->before('GET|POST|PUT|DELETE|HEAD', '/([a-z0-9_-]+)(/.*)?', function ($language, $slug = null) {

                // The given language does not appear in the array of allowed languages
                if (!in_array($language, $this->allowedLanguages)) {
                    header('location: /' . $this->defaultLanguage);
                    exit();
                }
            });
        }
    }

    // Create a Router
    $router = new MultilangRouter(
        ['en','nl','fr'], //= allowed languages
        'nl' // = default language
    );

    $router->get('/([a-z0-9_-]+)', function ($language) {
        exit('This is the ' . $language . ' index');
    });

    $router->get('/([a-z0-9_-]+)/([a-z0-9_-]+)', function ($language, $slug) {
        exit('This is the ' . $language . ' version of ' . $slug);
    });

    $router->get('/([a-z0-9_-]+)/(.*)', function ($language, $slug) {
        exit('This is the ' . $language . ' version of ' . $slug . ' (multiple segments allowed)');
    });

    // Thunderbirds are go!
    $router->run();

// EOF
