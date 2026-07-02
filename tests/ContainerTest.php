<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Container;

class ContainerTest extends TestCase
{
    protected function setUp(): void
    {
        $reflection = new \ReflectionClass(Container::class);
        $instanceProp = $reflection->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null, null);
    }

    public function testSingletonInstance()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';
        
        Container::getInstance()->bind('my_service', function() use ($obj) {
            return $obj;
        });

        $resolved = Container::getInstance()->make('my_service');
        $this->assertSame($obj, $resolved);
        
        $resolved2 = Container::getInstance()->make('my_service');
        $this->assertSame($resolved, $resolved2); // Assert it's the exact same instance
    }

    public function testBindReturnsNewInstance()
    {
        Container::getInstance()->bind('my_class', function() {
            return new \stdClass();
        });

        $instance1 = Container::getInstance()->make('my_class');
        $instance2 = Container::getInstance()->make('my_class');

        $this->assertInstanceOf(\stdClass::class, $instance1);
        $this->assertNotSame($instance1, $instance2); // Should be different instances
    }

    public function testResolveUnboundClass()
    {
        // Container should try to instantiate a class with no constructor args if not bound
        $instance = Container::getInstance()->make(\stdClass::class);
        $this->assertInstanceOf(\stdClass::class, $instance);
    }
}
