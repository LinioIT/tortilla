<?php

declare(strict_types=1);

namespace Linio\Tortilla\Route\ControllerResolver;

use Pimple\Container;

class ServiceControllerResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testIsGettingCallableController()
    {
        $expectedCallable = function () {
            return 'foo';
        };

        $resolver = new ServiceControllerResolver(new Container());
        $callable = $resolver->getController($expectedCallable);
        $this->assertEquals($expectedCallable, $callable);
    }

    public function testIsDetectingInvalidServiceController()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve provided controller: foobar');

        $resolver = new ServiceControllerResolver(new Container());
        $resolver->getController('foobar');
    }

    public function testIsDetectingUnregisteredServiceController()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve provided controller service: foobar');

        $resolver = new ServiceControllerResolver(new Container());
        $resolver->getController('foobar:indexAction');
    }

    public function testIsGettingServiceController()
    {
        $container = new Container();
        $container['foobar'] = function () {
            return new \StdClass();
        };

        $resolver = new ServiceControllerResolver($container);
        $callable = $resolver->getController('foobar:indexAction');
        $this->assertEquals([new \StdClass(), 'indexAction'], $callable);
    }
}
