<?php

declare(strict_types=1);

namespace Linio\Tortilla;

use Linio\Exception\HttpException;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testIsBuilding()
    {
        $app = new Application();
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher', $app['event.dispatcher']);
        $this->assertInstanceOf('Linio\Tortilla\Listener\JsonBodyListener', $app['application.json_body_listener']);
        $this->assertInstanceOf('Linio\Tortilla\Route\Dispatcher', $app['route.dispatcher']);
        $this->assertInstanceOf('Linio\Tortilla\Route\ControllerResolver\ServiceControllerResolver', $app['controller.resolver']);
        $this->assertInstanceOf('FastRoute\RouteParser\Std', $app['route.parser']);
        $this->assertInstanceOf('FastRoute\DataGenerator\GroupCountBased', $app['route.data_generator']);
        $this->assertInstanceOf('FastRoute\RouteCollector', $app['route.collector']);
    }

    public function testIsMappingGetRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute('GET', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->get('foo', 'bar');
    }

    public function testIsMappingPostRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute('POST', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->post('foo', 'bar');
    }

    public function testIsMappingPutRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute('PUT', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->put('foo', 'bar');
    }

    public function testIsMappingDeleteRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute('DELETE', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->delete('foo', 'bar');
    }

    public function testIsMappingPatchRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute('PATCH', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->patch('foo', 'bar');
    }

    public function testIsMappingOptionsRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute('OPTIONS', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->options('foo', 'bar');
    }

    public function testIsMappingRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize('FastRoute\RouteCollector');
        $routeCollector->addRoute(['GET', 'POST'], 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->match(['GET', 'POST'], 'foo', 'bar');
    }

    public function testIsHandlingRequests()
    {
        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setContent('barfoo');

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willReturn($expectedResponse);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testIsHandlingRequestEventWithResponse()
    {
        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setContent('barfoo');

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willReturn($expectedResponse);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher
            ->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))
            ->will(function ($args) use ($expectedResponse) {
                $args[1]->setResponse($expectedResponse);
            });

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testIsHandlingExceptions()
    {
        $request = Request::create('/foo/bar', 'GET');

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->log('alert', Argument::type('string'))->shouldBeCalled();

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willThrow(new \Exception());

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(ApplicationEvents::EXCEPTION, Argument::type('Linio\Tortilla\Event\ExceptionEvent'))->shouldBeCalled();

        $app = new Application();
        $app['logger'] = $logger->reveal();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);

        $this->assertEquals('{"error":{"message":"Internal error","code":500}}', $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testIsHandlingExceptionWithResponse()
    {
        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setContent('error!');

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->log('alert', Argument::type('string'))->shouldBeCalled();

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willThrow(new \Exception());

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();
        $eventDispatcher
            ->dispatch(ApplicationEvents::EXCEPTION, Argument::type('Linio\Tortilla\Event\ExceptionEvent'))
            ->will(function ($args) use ($expectedResponse) {
                $args[1]->setResponse($expectedResponse);
            });

        $app = new Application();
        $app['logger'] = $logger->reveal();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testIsHandlingExceptionsInDebug()
    {
        $request = Request::create('/foo/bar', 'GET');
        $exception = new \Exception();

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->log('alert', (string) $exception)->shouldBeCalled();

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willThrow($exception);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(ApplicationEvents::EXCEPTION, Argument::type('Linio\Tortilla\Event\ExceptionEvent'))->shouldBeCalled();

        $app = new Application();
        $app['debug'] = true;
        $app['logger'] = $logger->reveal();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);

        $this->assertEquals((string) $exception, $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testIsHandlingHttpExceptions()
    {
        $request = Request::create('/foo/bar', 'GET');
        $exception = new HttpException('foobar', 401, 1001);

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->log('warning', (string) $exception)->shouldBeCalled();

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willThrow($exception);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(ApplicationEvents::EXCEPTION, Argument::type('Linio\Tortilla\Event\ExceptionEvent'))->shouldBeCalled();

        $app = new Application();
        $app['logger'] = $logger->reveal();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);

        $this->assertEquals('{"error":{"message":"foobar","code":1001}}', $response->getContent());
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * @expectedException \Exception
     */
    public function testIsThrowingExceptions()
    {
        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setStatusCode(500);

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willThrow(new \Exception());

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $app->handle($request, 0, false);
    }

    public function testIsRunning()
    {
        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setContent('barfoo');

        $routeDispatcher = $this->prophesize('Linio\Tortilla\Route\Dispatcher');
        $routeDispatcher->handle($request)->willReturn($expectedResponse);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcher');
        $eventDispatcher->dispatch(ApplicationEvents::REQUEST, Argument::type('Linio\Tortilla\Event\RequestEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(ApplicationEvents::RESPONSE, Argument::type('Linio\Tortilla\Event\ResponseEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(ApplicationEvents::TERMINATE, Argument::type('Linio\Tortilla\Event\PostResponseEvent'))->shouldBeCalled();

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $app->run($request);
        $this->expectOutputString('barfoo');
    }
}
