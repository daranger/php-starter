<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Http\Middleware\CsrfMiddleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function testGetRequestPassesAndGeneratesToken()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET']);
        $middleware = new CsrfMiddleware();

        $response = $middleware->handle($request, function ($req) {
            return new Response('ok');
        });

        $getProp = function($obj, $prop) {
            $reflection = new \ReflectionClass($obj);
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);
            return $property->getValue($obj);
        };

        $this->assertEquals('ok', $getProp($response, 'content'));
        $this->assertNotEmpty(Session::token());
    }

    public function testPostRequestFailsWithoutToken()
    {
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $middleware = new CsrfMiddleware();

        $response = $middleware->handle($request, function ($req) {
            return new Response('ok');
        });

        $getProp = function($obj, $prop) {
            $reflection = new \ReflectionClass($obj);
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);
            return $property->getValue($obj);
        };

        $this->assertEquals(302, $getProp($response, 'status'));
        $headers = $getProp($response, 'headers');
        $this->assertEquals('/', $headers['Location'] ?? '');
    }

    public function testPostRequestPassesWithValidToken()
    {
        $token = Session::token();
        $request = new Request([], ['_csrf' => $token], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $middleware = new CsrfMiddleware();

        $response = $middleware->handle($request, function ($req) {
            return new Response('ok');
        });

        $getProp = function($obj, $prop) {
            $reflection = new \ReflectionClass($obj);
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);
            return $property->getValue($obj);
        };

        $this->assertEquals('ok', $getProp($response, 'content'));
    }
}
