<?php

namespace {

    /**
     * Run given callable with $_SERVER global variable set up to mimic a HTTP request.
     * @param callable(): void $fn.
     * @param array<string, string> $requestInfo values to override in $_SERVER.
     */
    function run_with_request_data($fn, $requestInfo)
    {
        $oldServer = $_SERVER;

        // Clear SCRIPT_NAME because bramus/router tries to guess the subfolder the script is run in
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Default SERVER_PROTOCOL method to HTTP/1.1
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $_SERVER = array_merge($_SERVER, $requestInfo);

        try {
            $fn();
            // Restore previous $_SERVER
            $_SERVER = $oldServer;
        } catch (\Exception $e) {
            // Restore previous $_SERVER
            // The finally block is not supported by PHP 5.4.
            $_SERVER = $oldServer;

            throw $e;
        }
    }

    /**
     * Helper to simulate running a router on a request.
     *
     * @param \Bramus\Router\Router $router
     * @param string $uri
     * @param string $requestMethod
     * @param callable|null $extraMiddleware Extra middleware to pass to router’s run method.
     * @param array<string, string> $requestInfo values to override in $_SERVER.
     * @param callable(string): void $test Test function taking response body.
     */
    function run_request_full(\Bramus\Router\Router $router, $requestMethod, $uri, $requestInfo, $extraMiddleware, $test)
    {
        $requestInfo['REQUEST_URI'] = $uri;
        $requestInfo['REQUEST_METHOD'] = $requestMethod;

        run_with_request_data(function () use ($router, $extraMiddleware, $test) {
            ob_start();
            if ($extraMiddleware === null) {
                $router->run();
            } else {
                $router->run($extraMiddleware);
            }
            $responseBody = ob_get_contents();
            ob_end_clean();

            $test($responseBody);
        }, $requestInfo);
    }

    function run_request(\Bramus\Router\Router $router, $requestMethod, $uri, $test)
    {
        run_request_full($router, $requestMethod, $uri, [], null, $test);
    }

    class Handler
    {
        public function notfound()
        {
            echo 'route not found';
        }
    }

    class RouterTest extends PHPUnit_Framework_TestCase
    {
        public function testInit()
        {
            $this->assertInstanceOf('\Bramus\Router\Router', new \Bramus\Router\Router());
        }

        public function testUri()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Fake some data
            run_with_request_data(function () {
                $method = new ReflectionMethod(
                    '\Bramus\Router\Router',
                    'getCurrentUri'
                );

                $method->setAccessible(true);

                $this->assertEquals(
                    '/about/whatever',
                    $method->invoke(new \Bramus\Router\Router())
                );
            }, [
                'SCRIPT_NAME' => '/sub/folder/index.php',
                'REQUEST_URI' => '/sub/folder/about/whatever',
            ]);
        }

        public function testBasePathOverride()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Fake some data
            $requestInfo = [
                'SCRIPT_NAME' => '/public/index.php',
                'REQUEST_URI' => '/about',
            ];

            run_with_request_data(function () use ($router) {
                $router->setBasePath('/');

                $this->assertEquals(
                    '/',
                    $router->getBasePath()
                );
            }, $requestInfo);

            // Test the /about route
            run_request_full($router, 'GET', '/about', $requestInfo, null, function ($responseBody) {
                $this->assertEquals('about', $responseBody);
            });
        }

        public function testBasePathThatContainsEmoji()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Fake some data
            $requestInfo = ['SCRIPT_NAME' => '/sub/folder/💩/index.php'];

            // Test the /hello/bramus route
            run_request_full($router, 'GET', '/sub/folder/%F0%9F%92%A9/about', $requestInfo, null, function ($responseBody) {
                $this->assertEquals('about', $responseBody);
            });
        }

        public function testStaticRoute()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Test the /about route
            run_request($router, 'GET', '/about', function ($responseBody) {
                $this->assertEquals('about', $responseBody);
            });
        }

        public function testStaticRouteUsingShorthand()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/about', function () {
                echo 'about';
            });

            // Test the /about route
            run_request($router, 'GET', '/about', function ($responseBody) {
                $this->assertEquals('about', $responseBody);
            });
        }

        public function testRequestMethods()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'get';
            });
            $router->post('/', function () {
                echo 'post';
            });
            $router->put('/', function () {
                echo 'put';
            });
            $router->patch('/', function () {
                echo 'patch';
            });
            $router->delete('/', function () {
                echo 'delete';
            });
            $router->options('/', function () {
                echo 'options';
            });

            // Test GET
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('get', $responseBody);
            });

            // Test POST
            run_request($router, 'POST', '/', function ($responseBody) {
                $this->assertEquals('post', $responseBody);
            });

            // Test PUT
            run_request($router, 'PUT', '/', function ($responseBody) {
                $this->assertEquals('put', $responseBody);
            });

            // Test PATCH
            run_request($router, 'PATCH', '/', function ($responseBody) {
                $this->assertEquals('patch', $responseBody);
            });

            // Test DELETE
            run_request($router, 'DELETE', '/', function ($responseBody) {
                $this->assertEquals('delete', $responseBody);
            });

            // Test OPTIONS
            run_request($router, 'OPTIONS', '/', function ($responseBody) {
                $this->assertEquals('options', $responseBody);
            });

            // Test HEAD
            run_request($router, 'HEAD', '/', function ($responseBody) {
                $this->assertEquals('', $responseBody);
            });
        }

        public function testArrayMatch()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match(['POST', 'PUT'], '/', function () {
                echo 'post|put';
            });

            $notFound = false;
            $router->set404(function () use (&$notFound) {
                $notFound = true;
            });

            // Test GET
            $notFound = false;
            run_request($router, 'GET', '/', function ($responseBody) use (&$notFound) {
                $this->assertTrue($notFound);
            });

            // Test POST
            $notFound = false;
            run_request($router, 'POST', '/', function ($responseBody) use (&$notFound) {
                $this->assertFalse($notFound);
                $this->assertEquals('post|put', $responseBody);
            });

            // Test PUT
            $notFound = false;
            run_request($router, 'PUT', '/', function ($responseBody) use (&$notFound) {
                $this->assertFalse($notFound);
                $this->assertEquals('post|put', $responseBody);
            });

            // Test DELETE
            $notFound = false;
            run_request($router, 'DELETE', '/', function ($responseBody) use (&$notFound) {
                $this->assertTrue($notFound);
            });

            // Test OPTIONS
            $notFound = false;
            run_request($router, 'OPTIONS', '/', function ($responseBody) use (&$notFound) {
                $this->assertTrue($notFound);
            });

            // Test PATCH
            $notFound = false;
            run_request($router, 'PATCH', '/', function ($responseBody) use (&$notFound) {
                $this->assertTrue($notFound);
            });

            // Test HEAD
            $notFound = false;
            run_request($router, 'HEAD', '/', function ($responseBody) use (&$notFound) {
                $this->assertTrue($notFound);
            });
        }

        public function testShorthandAll()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->all('/', function () {
                echo 'all';
            });

            // Test GET
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('all', $responseBody);
            });

            // Test POST
            run_request($router, 'POST', '/', function ($responseBody) {
                $this->assertEquals('all', $responseBody);
            });

            // Test PUT
            run_request($router, 'PUT', '/', function ($responseBody) {
                $this->assertEquals('all', $responseBody);
            });

            // Test DELETE
            run_request($router, 'DELETE', '/', function ($responseBody) {
                $this->assertEquals('all', $responseBody);
            });

            // Test OPTIONS
            run_request($router, 'OPTIONS', '/', function ($responseBody) {
                $this->assertEquals('all', $responseBody);
            });

            // Test PATCH
            run_request($router, 'PATCH', '/', function ($responseBody) {
                $this->assertEquals('all', $responseBody);
            });

            // Test HEAD
            run_request($router, 'HEAD', '/', function ($responseBody) {
                $this->assertEquals('', $responseBody);
            });
        }

        public function testDynamicRoute()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/(\w+)', function ($name) {
                echo 'Hello ' . $name;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus', function ($responseBody) {
                $this->assertEquals('Hello bramus', $responseBody);
            });
        }

        public function testDynamicRouteWithMultiple()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/(\w+)/(\w+)', function ($name, $lastname) {
                echo 'Hello ' . $name . ' ' . $lastname;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus/sumarb', function ($responseBody) {
                $this->assertEquals('Hello bramus sumarb', $responseBody);
            });
        }

        public function testCurlyBracesRoutes()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{name}/{lastname}', function ($name, $lastname) {
                echo 'Hello ' . $name . ' ' . $lastname;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus/sumarb', function ($responseBody) {
                $this->assertEquals('Hello bramus sumarb', $responseBody);
            });
        }

        public function testCurlyBracesRoutesWithNonAZCharsInPlaceholderNames()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{arg1}/{arg2}', function ($arg1, $arg2) {
                echo 'Hello ' . $arg1 . ' ' . $arg2;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus/sumarb', function ($responseBody) {
                $this->assertEquals('Hello bramus sumarb', $responseBody);
            });
        }

        public function testCurlyBracesRoutesWithCyrillicCharactersInPlaceholderNames()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{това}/{това}', function ($arg1, $arg2) {
                echo 'Hello ' . $arg1 . ' ' . $arg2;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus/sumarb', function ($responseBody) {
                $this->assertEquals('Hello bramus sumarb', $responseBody);
            });
        }

        public function testCurlyBracesRoutesWithEmojiInPlaceholderNames()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{😂}/{😅}', function ($arg1, $arg2) {
                echo 'Hello ' . $arg1 . ' ' . $arg2;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus/sumarb', function ($responseBody) {
                $this->assertEquals('Hello bramus sumarb', $responseBody);
            });
        }

        public function testCurlyBracesWithCyrillicCharacters()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/bg/{arg}', function ($arg) {
                echo 'BG: ' . $arg;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/bg/това', function ($responseBody) {
                $this->assertEquals('BG: това', $responseBody);
            });
        }

        public function testCurlyBracesWithMultipleCyrillicCharacters()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/bg/{arg}/{arg}', function ($arg1, $arg2) {
                echo 'BG: ' . $arg1 . ' - ' . $arg2;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/bg/това/слъг', function ($responseBody) {
                $this->assertEquals('BG: това - слъг', $responseBody);
            });
        }

        public function testCurlyBracesWithEmoji()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/emoji/{emoji}', function ($emoji) {
                echo 'Emoji: ' . $emoji;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/emoji/' . urlencode('💩'), function ($responseBody) {
                $this->assertEquals('Emoji: 💩', $responseBody);
            });
        }

        public function testCurlyBracesWithEmojiCombinedWithBasePathThatContainsEmoji()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/emoji/{emoji}', function ($emoji) {
                echo 'Emoji: ' . $emoji;
            });

            // Fake some data
            $requestInfo = ['SCRIPT_NAME' => '/sub/folder/💩/index.php'];

            run_request_full($router, 'GET', '/sub/folder/' . urlencode('💩') . '/emoji/' . urlencode('🤯'), $requestInfo, null, function ($responseBody) {
                $this->assertEquals('Emoji: 🤯', $responseBody);
            });
        }

        public function testDynamicRouteWithOptionalSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello(/\w+)?', function ($name = null) {
                echo 'Hello ' . (($name) ? $name : 'stranger');
            });

            // Test the /hello route
            run_request($router, 'GET', '/hello', function ($responseBody) {
                $this->assertEquals('Hello stranger', $responseBody);
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus', function ($responseBody) {
                $this->assertEquals('Hello bramus', $responseBody);
            });
        }

        public function testDynamicRouteWithMultipleSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/(.*)/page([0-9]+)', function ($place, $page) {
                echo 'Hello ' . $place . ' page : ' . $page;
            });

            // Test the /hello/bramus/page3 route
            run_request($router, 'GET', '/hello/bramus/page3', function ($responseBody) {
                $this->assertEquals('Hello hello/bramus page : 3', $responseBody);
            });
        }

        public function testDynamicRouteWithOptionalNestedSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function ($year = null, $month = null, $day = null, $slug = null) {
                if ($year === null) {
                    echo 'Blog overview';

                    return;
                }
                if ($month === null) {
                    echo 'Blog year overview (' . $year . ')';

                    return;
                }
                if ($day === null) {
                    echo 'Blog month overview (' . $year . '-' . $month . ')';

                    return;
                }
                if ($slug === null) {
                    echo 'Blog day overview (' . $year . '-' . $month . '-' . $day . ')';

                    return;
                }
                echo 'Blogpost ' . htmlentities($slug) . ' detail (' . $year . '-' . $month . '-' . $day . ')';
            });

            // Test the /blog route
            run_request($router, 'GET', '/blog', function ($responseBody) {
                $this->assertEquals('Blog overview', $responseBody);
            });

            // Test the /blog/year route
            run_request($router, 'GET', '/blog/1983', function ($responseBody) {
                $this->assertEquals('Blog year overview (1983)', $responseBody);
            });

            // Test the /blog/year/month route
            run_request($router, 'GET', '/blog/1983/12', function ($responseBody) {
                $this->assertEquals('Blog month overview (1983-12)', $responseBody);
            });

            // Test the /blog/year/month/day route
            run_request($router, 'GET', '/blog/1983/12/26', function ($responseBody) {
                $this->assertEquals('Blog day overview (1983-12-26)', $responseBody);
            });

            // Test the /blog/year/month/day/slug route
            run_request($router, 'GET', '/blog/1983/12/26/bramus', function ($responseBody) {
                $this->assertEquals('Blogpost bramus detail (1983-12-26)', $responseBody);
            });
        }

        public function testDynamicRouteWithNestedOptionalSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello(/\w+(/\w+)?)?', function ($name1 = null, $name2 = null) {
                echo 'Hello ' . (($name1) ? $name1 : 'stranger') . ' ' . (($name2) ? $name2 : 'stranger');
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus', function ($responseBody) {
                $this->assertEquals('Hello bramus stranger', $responseBody);
            });

            // Test the /hello/bramus/bramus route
            run_request($router, 'GET', '/hello/bramus/bramus', function ($responseBody) {
                $this->assertEquals('Hello bramus bramus', $responseBody);
            });
        }

        public function testDynamicRouteWithWildcard()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('(.*)', function ($name) {
                echo 'Hello ' . $name;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus', function ($responseBody) {
                $this->assertEquals('Hello hello/bramus', $responseBody);
            });
        }

        public function testDynamicRouteWithPartialWildcard()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/(.*)', function ($name) {
                echo 'Hello ' . $name;
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/hello/bramus/sumarb', function ($responseBody) {
                $this->assertEquals('Hello bramus/sumarb', $responseBody);
            });
        }

        public function test404()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });
            $router->set404(function ($handledByOtherMethod) {
                echo $handledByOtherMethod ? 'method not allowed' : 'route not found';
            });

            $router->set404('/api(/.*)?', function () {
                echo 'api route not found';
            });

            // Test existing route
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('home', $responseBody);
            });

            // Test route existing for other method
            run_request($router, 'POST', '/', function ($responseBody) {
                $this->assertEquals('method not allowed', $responseBody);
            });

            // Test non-existing route
            run_request($router, 'GET', '/foo', function ($responseBody) {
                $this->assertEquals('route not found', $responseBody);
            });

            // Test non-existing route
            run_request($router, 'POST', '/foo', function ($responseBody) {
                $this->assertEquals('route not found', $responseBody);
            });

            // Test the custom api 404
            run_request($router, 'POST', '/api/getUser', function ($responseBody) {
                $this->assertEquals('api route not found', $responseBody);
            });
        }

        public function test404WithClassAtMethod()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            $router->set404('Handler@notFound');

            // Test the /hello route
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('home', $responseBody);
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/foo', function ($responseBody) {
                $this->assertEquals('route not found', $responseBody);
            });
        }

        public function test404WithClassAtStaticMethod()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            $router->set404('Handler@notFound');

            // Test the /hello route
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('home', $responseBody);
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/foo', function ($responseBody) {
                $this->assertEquals('route not found', $responseBody);
            });
        }

        public function test404WithManualTrigger()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () use ($router) {
                $router->trigger404();
            });
            $router->set404(function () {
                echo 'route not found';
            });

            // Test the / route
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('route not found', $responseBody);
            });
        }

        public function testBeforeRouterMiddleware()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->before('GET|POST', '/.*', function () {
                echo 'before ';
            });
            $router->get('/', function () {
                echo 'root';
            });
            $router->get('/about', function () {
                echo 'about';
            });
            $router->get('/contact', function () {
                echo 'contact';
            });
            $router->post('/post', function () {
                echo 'post';
            });

            // Test the / route
            run_request($router, 'GET', '/', function ($responseBody) {
                $this->assertEquals('before root', $responseBody);
            });

            // Test the /about route
            run_request($router, 'GET', '/about', function ($responseBody) {
                $this->assertEquals('before about', $responseBody);
            });

            // Test the /contact route
            run_request($router, 'GET', '/contact', function ($responseBody) {
                $this->assertEquals('before contact', $responseBody);
            });

            // Test the /post route
            run_request($router, 'POST', '/post', function ($responseBody) {
                $this->assertEquals('before post', $responseBody);
            });
        }

        public function testAfterRouterMiddleware()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            $extraMiddleware = function () {
                echo 'finished';
            };

            // Test the / route
            run_request_full($router, 'GET', '/', [], $extraMiddleware, function ($responseBody) {
                $this->assertEquals('homefinished', $responseBody);
            });
        }

        public function testBasicController()
        {
            $router = new \Bramus\Router\Router();

            $router->get('/show/(.*)', 'RouterTestController@show');

            run_request($router, 'GET', '/show/foo', function ($responseBody) {
                $this->assertEquals('foo', $responseBody);
            });
        }

        public function testDefaultNamespace()
        {
            $router = new \Bramus\Router\Router();

            $router->setNamespace('\Hello');

            $router->get('/show/(.*)', 'HelloRouterTestController@show');

            run_request($router, 'GET', '/show/foo', function ($responseBody) {
                $this->assertEquals('foo', $responseBody);
            });
        }

        public function testSubfolders()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            // Test the / route in a fake subfolder
            $requestInfo = ['SCRIPT_NAME' => '/about/index.php'];
            run_request_full($router, 'GET', '/about/', $requestInfo, null, function ($responseBody) {
                $this->assertEquals('home', $responseBody);
            });
        }

        public function testSubrouteMouting()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->mount('/movies', function () use ($router) {
                $router->get('/', function () {
                    echo 'overview';
                });
                $router->get('/(\d+)', function ($id) {
                    echo htmlentities($id);
                });
            });

            // Test the /movies route
            run_request($router, 'GET', '/movies', function ($responseBody) {
                $this->assertEquals('overview', $responseBody);
            });

            // Test the /hello/bramus route
            run_request($router, 'GET', '/movies/1', function ($responseBody) {
                $this->assertEquals('1', $responseBody);
            });
        }

        public function testHttpMethodOverride()
        {
            $requestInfo = [
                'REQUEST_METHOD' => 'POST',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT',
            ];
            run_with_request_data(function () {
                // Fake the request method to being POST and override it

                $method = new ReflectionMethod(
                    '\Bramus\Router\Router',
                    'getRequestMethod'
                );

                $method->setAccessible(true);

                $this->assertEquals(
                    'PUT',
                    $method->invoke(new \Bramus\Router\Router())
                );
            }, $requestInfo);
        }

        public function testControllerMethodReturningFalse()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/false', 'RouterTestController@returnFalse');
            $router->get('/static-false', 'RouterTestController@staticReturnFalse');

            // Test returnFalse
            run_request($router, 'GET', '/false', function ($responseBody) {
                $this->assertEquals('returnFalse', $responseBody);
            });

            // Test staticReturnFalse
            run_request($router, 'GET', '/static-false', function ($responseBody) {
                $this->assertEquals('staticReturnFalse', $responseBody);
            });
        }
    }
}

namespace {
    class RouterTestController
    {
        public function show($id)
        {
            echo $id;
        }

        public function returnFalse()
        {
            echo 'returnFalse';

            return false;
        }

        public static function staticReturnFalse()
        {
            echo 'staticReturnFalse';

            return false;
        }
    }
}

namespace Hello {
    class HelloRouterTestController
    {
        public function show($id)
        {
            echo $id;
        }
    }
}

// EOF
