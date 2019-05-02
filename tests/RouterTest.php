<?php

namespace {

    class RouterTest extends PHPUnit_Framework_TestCase
    {
        public function testInit()
        {
            $this->assertInstanceOf('\Bramus\Router\Router', new \Bramus\Router\Router());
        }

        public function testCloneRouter()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->get('/info', function () {
                echo 'yes';
            });

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'yes',
                ob_get_clean()
            );
        }

        public function testCloneRouter2()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->get('/info', 'MyNamespace\MyController@method');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController@method value',
                ob_get_clean()
            );
        }

        public function testCloneRouter3()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->get('/info', 'MyNamespace\MyController::staticMethod');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testCloneRouter4()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->ns('MyNamespace')->prefix('/novel')->get('/info', 'MyController@method');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController@method value',
                ob_get_clean()
            );
        }

        public function testCloneRouter5()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->ns('MyNamespace')->prefix('/novel')->get('/info', 'MyController::staticMethod');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testCloneRouter6()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->get('/info', 'MyNamespace\MyController::staticMethod');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testCloneRouter7()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->group(function (\Bramus\Router\Router $router2) {
                $router2->get('info', 'MyNamespace\MyController@method');
            });

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController@method value',
                ob_get_clean()
            );
        }

        public function testCloneRouter8()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->group(function (\Bramus\Router\Router $router2) {
                $router2->get('info', 'MyNamespace\MyController::staticMethod');
            });

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testCloneRouter9()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router2 = clone $router;

            // Define routes
            $router2->prefix('/novel')->group(function (\Bramus\Router\Router $router2) {
                $router2->get('info', 'MyNamespace\MyController::staticMethod');
            });

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testFullNamespace()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $router->ns('MyNamespace')->prefix('/novel')->get('/info', '\\MyNamespace\\MyController@method');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController@method value',
                ob_get_clean()
            );
        }

        public function testFullNamespace2()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $router->prefix('/novel')->get('/info', '\\MyNamespace\\MyController@method');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController@method value',
                ob_get_clean()
            );
        }

        public function testFullNamespace3()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $router->ns('MyNamespace')->prefix('/novel')->get('/info', '\\MyNamespace\\MyController::staticMethod');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testFullNamespace4()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $router->prefix('/novel')->get('/info', '\\MyNamespace\\MyController::staticMethod');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals(
                'MyNamespace\MyController::staticMethod value',
                ob_get_clean()
            );
        }

        public function testPrefix()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $router->prefix('/novel')->get('/nothing', function () {
                echo 'no';
            });

            ob_start();
            $router->get('/novel/info', function () {
                echo 'yes';
            });

            // Run it!
            $router->run();

            $this->assertEquals(
                'yes',
                ob_get_clean()
            );
        }

        public function testPrefix2()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $cc = function (\Bramus\Router\Router $router) {
                $router->get('/nothing', function () {
                    echo 'no';
                });
            };
            $cc($router->prefix('/novel'));

            ob_start();
            $router->get('/novel/info', function () {
                echo 'yes';
            });

            // Run it!
            $router->run();

            $this->assertEquals(
                'yes',
                ob_get_clean()
            );
        }

        public function testPrefix3()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Define routes
            $cc = function (\Bramus\Router\Router $router) {
                $router->group(function (\Bramus\Router\Router $router) {
                    $router->get('/novel/info', function () {
                        echo 'yes';
                    });
                });
            };
            $cc($router->prefix('/novel'));

            ob_start();
            $router->get('/novel/info', function () {
                echo 'yes';
            });

            // Run it!
            $router->run();

            $this->assertEquals(
                'yes',
                ob_get_clean()
            );
        }

        public function testNs()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->get('/login', 'LoginController@login1');
            $router->get('/login', 'LoginController@login2');

            $property = new ReflectionProperty('\Bramus\Router\Router', 'attributes');
            $property->setAccessible(true);
            $attributes = $property->getValue($router);

            $routers = $attributes->afterRoutes;

            list($r1, $r2) = $routers['GET'];

            $this->assertEquals('/admin/login', $r1['pattern']);
            $this->assertEquals('Admin\\LoginController@login1', $r1['fn']);

            $this->assertEquals('/login', $r2['pattern']);
            $this->assertEquals('LoginController@login2', $r2['fn']);
        }

        public function testGroup()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
            });

            $property = new ReflectionProperty('\Bramus\Router\Router', 'attributes');
            $property->setAccessible(true);
            $attributes = $property->getValue($router);

            $routers = $attributes->afterRoutes;

            list($r1) = $routers['GET'];

            $this->assertEquals('/admin/login', $r1['pattern']);
            $this->assertEquals('Admin\\LoginController@login3', $r1['fn']);
        }

        public function testSubGroup()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', 'Test3::test');
                });
            });

            $property = new ReflectionProperty('\Bramus\Router\Router', 'attributes');
            $property->setAccessible(true);
            $attributes = $property->getValue($router);

            $routers = $attributes->afterRoutes;

            list($r1, $r2, $r3) = $routers['GET'];

            $this->assertEquals('/admin/login', $r1['pattern']);
            $this->assertEquals('Admin\\LoginController@login3', $r1['fn']);

            $this->assertEquals('/admin/novel/info', $r2['pattern']);
            $this->assertEquals('Admin\\Novel\\NovelController@getNovelInfo', $r2['fn']);

            $this->assertEquals('/admin/novel/(\w+)', $r3['pattern']);
            $this->assertEquals('Test3::test', $r3['fn']);

            ob_start();
            $router->run();
            $this->assertEquals('Test3 hello', ob_get_clean());
        }

        public function testDomain()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', function () {
                        echo 'admin.router.com 456';
                    });
                });
            });

            $router->domain('admin.router.com')->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', function () {
                        echo 'admin.router.com 123';
                    });
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('admin.router.com 123', ob_get_clean());
        }

        public function testDomain2()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', function () {
                        echo 'admin.router.com 456';
                    });
                });
            });

            $router->domain('admin.router.com')->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController@method value', ob_get_clean());
        }

        public function testDomain3()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('(\w+)', '\MyNamespace\MyController@method2');
                });
            });

            $router->domain('admin.router.com')->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            $method = new ReflectionMethod('\Bramus\Router\Router', 'getCurrentDomain');
            $method->setAccessible(true);
            $domain = $method->invoke($router);

            $this->assertEquals('admin.router.com', $domain);

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController@method value', ob_get_clean());
        }

        public function testDomain4()
        {
            $_SERVER['REQUEST_URI'] = '/admin/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('(\w+)', '\MyNamespace\MyController@method2');
            });

            $router->domain('admin.router.com')->ns('MyNamespace')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('(\w+)', 'MyController@method');
            });

            $property = new ReflectionProperty('\Bramus\Router\Router', 'attributes');
            $property->setAccessible(true);
            $attributes = $property->getValue($router);

            $routers = $attributes->afterRoutes;

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController@method value', ob_get_clean());
        }

        public function testDomain5()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            $router->domain('admin.router.com')->ns('MyNamespace')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->get('info', 'NovelController@getNovelInfo');
                $router->get('(\w+)', 'MyController::staticMethod');
                $router->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', 'MyController::staticMethod');
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController::staticMethod value', ob_get_clean());
        }

        public function testSubDomain()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            $router->domain('router.com')->ns('MyNamespace')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->get('info', 'NovelController@getNovelInfo');
                $router->get('(\w+)', 'MyController::staticMethod');
                $router->domain('admin')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', 'MyController::staticMethod');
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController::staticMethod value', ob_get_clean());
        }

        public function testSubDomain2()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            $router->domain('router.com')->ns('MyNamespace')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->get('info', 'NovelController@getNovelInfo');
                $router->get('(\w+)', 'MyController::staticMethod');
                $router->domain('admin.', '')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', 'MyController::staticMethod');
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController::staticMethod value', ob_get_clean());
        }

        public function testSubDomain3()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            $router->domain('com')->ns('MyNamespace')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->get('info', 'NovelController@getNovelInfo');
                $router->get('(\w+)', 'MyController::staticMethod');
                $router->domain('admin.router')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', 'MyController::staticMethod');
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController::staticMethod value', ob_get_clean());
        }

        public function testSubDomain4()
        {
            $_SERVER['REQUEST_URI'] = '/admin/novel/aaa?p1=1';
            $_SERVER['HTTP_HOST'] = 'admin.router.com';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', '\MyNamespace\MyController@method');
                });
            });

            $router->domain('er.com')->ns('MyNamespace')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->get('info', 'NovelController@getNovelInfo');
                $router->get('(\w+)', 'MyController::staticMethod');
                $router->domain('admin.rout', '')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                    $router->get('(\w+)', 'MyController::staticMethod');
                });
            });

            ob_start();
            $router->run();
            $this->assertEquals('MyNamespace\MyController::staticMethod value', ob_get_clean());
        }

        public function testStaticMethod()
        {
            $_SERVER['REQUEST_URI'] = '/account';

            // Create Router instance
            $router = new \Bramus\Router\Router();
            $router->get('/account', 'Test3::test');

            ob_start();
            // Run it!
            $router->run();

            $this->assertEquals('Test3 hello', ob_get_clean());
        }

        public function testMountRecovery()
        {
            $_SERVER['REQUEST_URI'] = '/novel/info?p1=1';

            // Create Router instance
            $router = new \Bramus\Router\Router();

            // Subrouting
            $router->mount('/movies', function () use ($router) {

                // will result in '/movies'
                $router->get('/', function () {
                    echo 'movies overview';
                });

                // will result in '/movies'
                $router->post('/', function () {
                    echo 'add movie';
                });

                // will result in '/movies/id'
                $router->get('/(\d+)', function ($id) {
                    echo 'movie id ' . htmlentities($id);
                });

                // will result in '/movies/id'
                $router->put('/(\d+)', function ($id) {
                    echo 'Update movie id ' . htmlentities($id);
                });
            });

            $router->ns('Admin')->prefix('/admin')->group(function (\Bramus\Router\Router $router) {
                $router->get('login', 'LoginController@login3');
                $router->ns('Novel')->prefix('/novel')->group(function (\Bramus\Router\Router $router) {
                    $router->get('info', 'NovelController@getNovelInfo');
                });
            });

            $property = new ReflectionProperty('\Bramus\Router\Router', 'attributes');
            $property->setAccessible(true);
            $attributes = $property->getValue($router);

            $routers = $attributes->afterRoutes;

            list($r1, $r2, $r3, $r4) = $routers['GET'];

            $this->assertEquals('/admin/novel/info', $r4['pattern']);
            $this->assertEquals('Admin\\Novel\\NovelController@getNovelInfo', $r4['fn']);
        }

        public function testUri()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Fake some data
            $_SERVER['SCRIPT_NAME'] = '/sub/folder/index.php';
            $_SERVER['REQUEST_URI'] = '/sub/folder/about/whatever';

            $method = new ReflectionMethod(
                '\Bramus\Router\Router',
                'getCurrentUri'
            );

            $method->setAccessible(true);

            $this->assertEquals(
                '/about/whatever',
                $method->invoke(new \Bramus\Router\Router())
            );
        }

        public function testBasePathOverride()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Fake some data
            $_SERVER['SCRIPT_NAME'] = '/public/index.php';
            $_SERVER['REQUEST_URI'] = '/about';

            $router->setBasePath('/');

            $this->assertEquals(
                '/',
                $router->getBasePath()
            );

            // Test the /about route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/about';
            $router->run();
            $this->assertEquals('about', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testBasePathThatContainsEmoji()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Fake some data
            $_SERVER['SCRIPT_NAME'] = '/sub/folder/💩/index.php';
            $_SERVER['REQUEST_URI'] = '/sub/folder/%F0%9F%92%A9/about';

            // Test the /hello/bramus route
            ob_start();
            $router->run();
            $this->assertEquals('about', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testStaticRoute()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->match('GET', '/about', function () {
                echo 'about';
            });

            // Test the /about route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/about';
            $router->run();
            $this->assertEquals('about', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testStaticRouteUsingShorthand()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/about', function () {
                echo 'about';
            });

            // Test the /about route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/about';
            $router->run();
            $this->assertEquals('about', ob_get_contents());

            // Cleanup
            ob_end_clean();
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
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run();
            $this->assertEquals('get', ob_get_contents());

            // Test POST
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $router->run();
            $this->assertEquals('post', ob_get_contents());

            // Test PUT
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'PUT';
            $router->run();
            $this->assertEquals('put', ob_get_contents());

            // Test PATCH
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'PATCH';
            $router->run();
            $this->assertEquals('patch', ob_get_contents());

            // Test DELETE
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'DELETE';
            $router->run();
            $this->assertEquals('delete', ob_get_contents());

            // Test OPTIONS
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
            $router->run();
            $this->assertEquals('options', ob_get_contents());

            // Test HEAD
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'HEAD';
            $router->run();
            $this->assertEquals('', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testShorthandAll()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->all('/', function () {
                echo 'all';
            });

            $_SERVER['REQUEST_URI'] = '/';

            // Test GET
            ob_start();
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $router->run();
            $this->assertEquals('all', ob_get_contents());

            // Test POST
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $router->run();
            $this->assertEquals('all', ob_get_contents());

            // Test PUT
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'PUT';
            $router->run();
            $this->assertEquals('all', ob_get_contents());

            // Test DELETE
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'DELETE';
            $router->run();
            $this->assertEquals('all', ob_get_contents());

            // Test OPTIONS
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
            $router->run();
            $this->assertEquals('all', ob_get_contents());

            // Test PATCH
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'PATCH';
            $router->run();
            $this->assertEquals('all', ob_get_contents());

            // Test HEAD
            ob_clean();
            $_SERVER['REQUEST_METHOD'] = 'HEAD';
            $router->run();
            $this->assertEquals('', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRoute()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/(\w+)', function ($name) {
                echo 'Hello ' . $name;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus';
            $router->run();
            $this->assertEquals('Hello bramus', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithMultiple()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/(\w+)/(\w+)', function ($name, $lastname) {
                echo 'Hello ' . $name . ' ' . $lastname;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';
            $router->run();
            $this->assertEquals('Hello bramus sumarb', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesRoutes()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{name}/{lastname}', function ($name, $lastname) {
                echo 'Hello ' . $name . ' ' . $lastname;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';
            $router->run();
            $this->assertEquals('Hello bramus sumarb', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesRoutesWithNonAZCharsInPlaceholderNames()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{arg1}/{arg2}', function ($arg1, $arg2) {
                echo 'Hello ' . $arg1 . ' ' . $arg2;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';
            $router->run();
            $this->assertEquals('Hello bramus sumarb', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesRoutesWithCyrillicCharactersInPlaceholderNames()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{това}/{това}', function ($arg1, $arg2) {
                echo 'Hello ' . $arg1 . ' ' . $arg2;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';
            $router->run();
            $this->assertEquals('Hello bramus sumarb', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesRoutesWithEmojiInPlaceholderNames()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/{😂}/{😅}', function ($arg1, $arg2) {
                echo 'Hello ' . $arg1 . ' ' . $arg2;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';
            $router->run();
            $this->assertEquals('Hello bramus sumarb', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesWithCyrillicCharacters()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/bg/{arg}', function ($arg) {
                echo 'BG: ' . $arg;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/bg/това';
            $router->run();
            $this->assertEquals('BG: това', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesWithMultipleCyrillicCharacters()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/bg/{arg}/{arg}', function ($arg1, $arg2) {
                echo 'BG: ' . $arg1 . ' - ' . $arg2;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/bg/това/слъг';
            $router->run();
            $this->assertEquals('BG: това - слъг', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesWithEmoji()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/emoji/{emoji}', function ($emoji) {
                echo 'Emoji: ' . $emoji;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/emoji/%F0%9F%92%A9'; // 💩
            $router->run();
            $this->assertEquals('Emoji: 💩', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testCurlyBracesWithEmojiCombinedWithBasePathThatContainsEmoji()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/emoji/{emoji}', function ($emoji) {
                echo 'Emoji: ' . $emoji;
            });

            // Fake some data
            $_SERVER['SCRIPT_NAME'] = '/sub/folder/💩/index.php';
            $_SERVER['REQUEST_URI'] = '/sub/folder/%F0%9F%92%A9/emoji/%F0%9F%A4%AF'; // 🤯

            // Test the /hello/bramus route
            ob_start();
            $router->run();
            $this->assertEquals('Emoji: 🤯', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithOptionalSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello(/\w+)?', function ($name = null) {
                echo 'Hello ' . (($name) ? $name : 'stranger');
            });

            // Test the /hello route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello';
            $router->run();
            $this->assertEquals('Hello stranger', ob_get_contents());

            // Test the /hello/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/hello/bramus';
            $router->run();
            $this->assertEquals('Hello bramus', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithMultipleSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/(.*)/page([0-9]+)', function ($place, $page) {
                echo 'Hello ' . $place . ' page : ' . $page;
            });

            // Test the /hello/bramus/page3 route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/page3';
            $router->run();
            $this->assertEquals('Hello hello/bramus page : 3', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithOptionalNestedSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', function ($year = null, $month = null, $day = null, $slug = null) {
                if (!$year) {
                    echo 'Blog overview';

                    return;
                }
                if (!$month) {
                    echo 'Blog year overview (' . $year . ')';

                    return;
                }
                if (!$day) {
                    echo 'Blog month overview (' . $year . '-' . $month . ')';

                    return;
                }
                if (!$slug) {
                    echo 'Blog day overview (' . $year . '-' . $month . '-' . $day . ')';

                    return;
                }
                echo 'Blogpost ' . htmlentities($slug) . ' detail (' . $year . '-' . $month . '-' . $day . ')';
            });

            // Test the /blog route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/blog';
            $router->run();
            $this->assertEquals('Blog overview', ob_get_contents());

            // Test the /blog/year route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/blog/1983';
            $router->run();
            $this->assertEquals('Blog year overview (1983)', ob_get_contents());

            // Test the /blog/year/month route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/blog/1983/12';
            $router->run();
            $this->assertEquals('Blog month overview (1983-12)', ob_get_contents());

            // Test the /blog/year/month/day route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/blog/1983/12/26';
            $router->run();
            $this->assertEquals('Blog day overview (1983-12-26)', ob_get_contents());

            // Test the /blog/year/month/day/slug route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/blog/1983/12/26/bramus';
            $router->run();
            $this->assertEquals('Blogpost bramus detail (1983-12-26)', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithNestedOptionalSubpatterns()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello(/\w+(/\w+)?)?', function ($name1 = null, $name2 = null) {
                echo 'Hello ' . (($name1) ? $name1 : 'stranger') . ' ' . (($name2) ? $name2 : 'stranger');
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus';
            $router->run();
            $this->assertEquals('Hello bramus stranger', ob_get_contents());

            // Test the /hello/bramus/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/bramus';
            $router->run();
            $this->assertEquals('Hello bramus bramus', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithWildcard()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('(.*)', function ($name) {
                echo 'Hello ' . $name;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus';
            $router->run();
            $this->assertEquals('Hello hello/bramus', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testDynamicRouteWithPartialWildcard()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/hello/(.*)', function ($name) {
                echo 'Hello ' . $name;
            });

            // Test the /hello/bramus route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';
            $router->run();
            $this->assertEquals('Hello bramus/sumarb', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function test404()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });
            $router->set404(function () {
                echo 'route not found';
            });

            // Test the /hello route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run();
            $this->assertEquals('home', ob_get_contents());

            // Test the /hello/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/foo';
            $router->run();
            $this->assertEquals('route not found', ob_get_contents());

            // Cleanup
            ob_end_clean();
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
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run();
            $this->assertEquals('home', ob_get_contents());

            // Test the /hello/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/foo';
            $router->run();
            $this->assertEquals('route not found', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function test404WithClassAtMethod2()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            $router->ns('Hello')->set404('HelloRouterTestController@notFound');

            // Test the /hello route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run();
            $this->assertEquals('home', ob_get_contents());

            // Test the /hello/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/foo';
            $router->run();
            $this->assertEquals('route not found 2', ob_get_contents());

            // Cleanup
            ob_end_clean();
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
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run();
            $this->assertEquals('home', ob_get_contents());

            // Test the /hello/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/foo';
            $router->run();
            $this->assertEquals('route not found', ob_get_contents());

            // Cleanup
            ob_end_clean();
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
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run();
            $this->assertEquals('before root', ob_get_contents());

            // Test the /about route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/about';
            $router->run();
            $this->assertEquals('before about', ob_get_contents());

            // Test the /contact route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/contact';
            $router->run();
            $this->assertEquals('before contact', ob_get_contents());

            // Test the /post route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/post';
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $router->run();
            $this->assertEquals('before post', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testAfterRouterMiddleware()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            // Test the / route
            ob_start();
            $_SERVER['REQUEST_URI'] = '/';
            $router->run(function () {
                echo 'finished';
            });
            $this->assertEquals('homefinished', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testBasicController()
        {
            $router = new \Bramus\Router\Router();

            $router->get('/show/(.*)', 'RouterTestController@show');

            ob_start();
            $_SERVER['REQUEST_URI'] = '/show/foo';
            $router->run();

            $this->assertEquals('foo', ob_get_contents());

            // cleanup
            ob_end_clean();
        }

        public function testDefaultNamespace()
        {
            $router = new \Bramus\Router\Router();

            $router->setNamespace('\Hello');

            $router->get('/show/(.*)', 'HelloRouterTestController@show');

            ob_start();
            $_SERVER['REQUEST_URI'] = '/show/foo';
            $router->run();

            $this->assertEquals('foo', ob_get_contents());

            // cleanup
            ob_end_clean();
        }

        public function testSubfolders()
        {
            // Create Router
            $router = new \Bramus\Router\Router();
            $router->get('/', function () {
                echo 'home';
            });

            // Test the / route in a fake subfolder
            ob_start();
            $_SERVER['SCRIPT_NAME'] = '/about/index.php';
            $_SERVER['REQUEST_URI'] = '/about/';
            $router->run();
            $this->assertEquals('home', ob_get_contents());

            // Cleanup
            ob_end_clean();
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
            ob_start();
            $_SERVER['REQUEST_URI'] = '/movies';
            $router->run();
            $this->assertEquals('overview', ob_get_contents());

            // Test the /hello/bramus route
            ob_clean();
            $_SERVER['REQUEST_URI'] = '/movies/1';
            $router->run();
            $this->assertEquals('1', ob_get_contents());

            // Cleanup
            ob_end_clean();
        }

        public function testHttpMethodOverride()
        {
            // Fake the request method to being POST and override it
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';

            $method = new ReflectionMethod(
                '\Bramus\Router\Router',
                'getRequestMethod'
            );

            $method->setAccessible(true);

            $this->assertEquals(
                'PUT',
                $method->invoke(new \Bramus\Router\Router())
            );
        }

        protected function setUp()
        {
            // Clear SCRIPT_NAME because bramus/router tries to guess the subfolder the script is run in
            $_SERVER['SCRIPT_NAME'] = '/index.php';

            // Default request method to GET
            $_SERVER['REQUEST_METHOD'] = 'GET';

            // Default SERVER_PROTOCOL method to HTTP/1.1
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        }

        protected function tearDown()
        {
            // nothing
        }
    }
}

namespace {

    class Handler
    {
        public function notfound()
        {
            echo 'route not found';
        }
    }

    class Test3
    {
        public static function test()
        {
            echo 'Test3 hello';

            return false;
        }
    }

    class Test4
    {
        public static function test2()
        {
            echo 'Test4 hello';

            return false;
        }

        public function test1()
        {
            echo 'Test4 hello';

            return false;
        }
    }

    class RouterTestController
    {
        public function show($id)
        {
            echo $id;
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

        public function notFound()
        {
            echo 'route not found 2';
        }
    }
}

// MyNamespace\MyController@method
namespace MyNamespace {
    class MyController
    {
        public static function staticMethod()
        {
            echo 'MyNamespace\MyController::staticMethod value';
        }

        public function method()
        {
            echo 'MyNamespace\MyController@method value';
        }

        public function method2()
        {
            echo 'MyNamespace\MyController@method2 value';
        }
    }
}

// EOF
