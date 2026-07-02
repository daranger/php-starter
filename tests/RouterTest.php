<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset router state via reflection since it's using static properties
        $reflection = new \ReflectionClass(Router::class);
        $routesProp = $reflection->getProperty('routes');
        $routesProp->setAccessible(true);
        $routesProp->setValue(null, []);
        
        $namedProp = $reflection->getProperty('namedRoutes');
        $namedProp->setAccessible(true);
        $namedProp->setValue(null, []);
    }

    public function testAddRouteAndMatch()
    {
        Router::get('/test', function() {
            return 'success';
        });

        // Simulate a request manually to test internal mapping
        $reflection = new \ReflectionClass(Router::class);
        $routesProp = $reflection->getProperty('routes');
        $routesProp->setAccessible(true);
        $routes = $routesProp->getValue();

        $this->assertCount(1, $routes['GET']);
        $this->assertArrayHasKey('/test', $routes['GET']);
        $this->assertEquals('/test', $routes['GET']['/test']['uri']);
    }

    public function testRouteGroupPrefix()
    {
        Router::group(['prefix' => '/api'], function() {
            Router::get('/users', function() {});
        });

        $reflection = new \ReflectionClass(Router::class);
        $routesProp = $reflection->getProperty('routes');
        $routesProp->setAccessible(true);
        $routes = $routesProp->getValue();

        $this->assertCount(1, $routes['GET']);
        $this->assertArrayHasKey('/api/users', $routes['GET']);
        $this->assertEquals('/api/users', $routes['GET']['/api/users']['uri']);
    }

    public function testNamedRoutes()
    {
        Router::get('/dashboard', function() {})->name('dashboard');

        $this->assertEquals('/dashboard', Router::route('dashboard'));
    }
}
