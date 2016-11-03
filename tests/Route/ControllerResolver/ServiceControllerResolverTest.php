<?php

declare(strict_types=1);

namespace Linio\Tortilla\Route\ControllerResolver;

use Pimple\Container;

class ServiceControllerResolverTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to resolve provided controller: foobar
     */
    public function testIsDetectingInvalidServiceController()
    {
        $resolver = new ServiceControllerResolver(new Container());
        $resolver->getController('foobar');
    }

    public function testIsSwitchingContainers()
    {
        $resolver = new ServiceControllerResolver(new Container());
        $resolver->setContainer(new Container());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to resolve provided controller service: foobar
     */
    public function testIsDetectingUnregisteredServiceController()
    {
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
