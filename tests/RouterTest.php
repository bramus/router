<?php

class RouterTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {

		// Clear SCRIPT_NAME because bramus/router tries to guess the subfolder the script is run in
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Default request method to GET
		$_SERVER['REQUEST_METHOD'] = 'GET';

	}

	protected function tearDown() {
		// nothing
	}

	public function testInit() {
		$this->assertInstanceOf('\Bramus\Router\Router', new \Bramus\Router\Router());
	}

	public function testUri() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->match('GET', '/about', function() {
			echo 'about';
		});

		// Fake some data
		$_SERVER['SCRIPT_NAME'] = '/sub/folder/index.php';
		$_SERVER['REQUEST_URI'] = '/sub/folder/about/whatever';

		$method = new ReflectionMethod(
			'\Bramus\Router\Router', 'getCurrentUri'
		);

		$method->setAccessible(TRUE);

		$this->assertEquals(
			'/about/whatever', $method->invoke(new \Bramus\Router\Router())
		);

	}

	public function testStaticRoute() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->match('GET', '/about', function() {
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

	public function testStaticRouteUsingShorthand() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/about', function() {
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

	public function testRequestMethods() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/', function() { echo 'get'; });
		$router->post('/', function() { echo 'post'; });
		$router->put('/', function() { echo 'put'; });
		$router->delete('/', function() { echo 'delete'; });
		$router->options('/', function() { echo 'options'; });

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

		// Cleanup
		ob_end_clean();

	}

	public function testDynamicRoute() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/hello/(\w+)', function($name) {
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

	public function testDynamicRouteWithOptionalSubpatterns() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/hello(/\w+)?', function($name = null) {
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

	/**
     * @runInSeparateProcess
     */
	public function testDefault404() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/', function() {
			echo 'home';
		});

		// Test the /hello route
		ob_clean();
		$_SERVER['REQUEST_URI'] = '/foo';
		$router->run();
		$headers = xdebug_get_headers(); // @todo: this is empty??!
		$this->assertEquals('', ob_get_contents());

		// Cleanup
		ob_end_clean();

	}

	public function test404() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/', function() {
			echo 'home';
		});
		$router->set404(function() {
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

	public function testBeforeRouteMiddleware() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->before('GET', '/about', function() {
			echo 'before ';
		});
		$router->get('/about', function() {
			echo 'about';
		});
		$router->get('/contact', function() {
			echo 'contact';
		});

		// Test the /about route
		ob_start();
		$_SERVER['REQUEST_URI'] = '/about';
		$router->run();
		$this->assertContains('before', ob_get_contents());

		// Test the /contact route
		ob_clean();
		$_SERVER['REQUEST_URI'] = '/contact';
		$router->run();
		$this->assertNotContains('before', ob_get_contents());

		// Cleanup
		ob_end_clean();

	}

	public function testBeforeRouterMiddleware() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->before('GET', '/.*', function() {
			echo 'before ';
		});
		$router->get('/about', function() {
			echo 'about';
		});
		$router->get('/contact', function() {
			echo 'contact';
		});

		// Test the /about route
		ob_start();
		$_SERVER['REQUEST_URI'] = '/about';
		$router->run();
		$this->assertContains('before', ob_get_contents());

		// Test the /contact route
		ob_clean();
		$_SERVER['REQUEST_URI'] = '/contact';
		$router->run();
		$this->assertContains('before', ob_get_contents());

		// Cleanup
		ob_end_clean();

	}

	public function testAfterRouterMiddleware() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/', function() {
			echo 'home';
		});

		// Test the / route
		ob_start();
		$_SERVER['REQUEST_URI'] = '/';
		$router->run(function() { echo 'finished'; });
		$this->assertEquals('homefinished', ob_get_contents());

		// Cleanup
		ob_end_clean();

	}

	public function testSubfolders() {

		// Create Router
		$router = new \Bramus\Router\Router();
		$router->get('/', function() {
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

}

// EOF