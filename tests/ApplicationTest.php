<?php

declare(strict_types=1);

namespace Linio\Tortilla;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Linio\Exception\HttpException;
use Linio\Tortilla\Event\ExceptionEvent;
use Linio\Tortilla\Event\PostResponseEvent;
use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Linio\Tortilla\Listener\JsonRequestBody;
use Linio\Tortilla\Route\ControllerResolver\ServiceControllerResolver;
use Linio\Tortilla\Route\Dispatcher;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends TestCase
{
    use ProphecyTrait;

    public function testIsBuilding()
    {
        $app = new Application();
        $this->assertInstanceOf(EventDispatcher::class, $app['event.dispatcher']);
        $this->assertInstanceOf(Dispatcher::class, $app['route.dispatcher']);
        $this->assertInstanceOf(RouteCollector::class, $app['route.collector']);
        $this->assertInstanceOf(JsonRequestBody::class, $app['application.json_request_body']);
        $this->assertInstanceOf(ServiceControllerResolver::class, $app['controller.resolver']);
        $this->assertInstanceOf(Std::class, $app['route.parser']);
        $this->assertInstanceOf(GroupCountBased::class, $app['route.data_generator']);
    }

    public function testIsMappingGetRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute('GET', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->get('foo', 'bar');
    }

    public function testIsMappingPostRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute('POST', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->post('foo', 'bar');
    }

    public function testIsMappingPutRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute('PUT', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->put('foo', 'bar');
    }

    public function testIsMappingDeleteRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute('DELETE', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->delete('foo', 'bar');
    }

    public function testIsMappingPatchRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute('PATCH', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->patch('foo', 'bar');
    }

    public function testIsMappingOptionsRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute('OPTIONS', 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->options('foo', 'bar');
    }

    public function testIsMappingRoutes()
    {
        $app = new Application();
        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->addRoute(['GET', 'POST'], 'foo', 'bar')->shouldBeCalled();
        $app['route.collector'] = $routeCollector->reveal();

        $app->match(['GET', 'POST'], 'foo', 'bar');
    }

    public function testIsHandlingRequests()
    {
        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setContent('barfoo');

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willReturn($expectedResponse);

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();

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

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willReturn($expectedResponse);

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher
            ->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)
            ->will(function ($args) use ($expectedResponse) {
                $args[1]->setResponse($expectedResponse);
            })
            ->willReturn($expectedResponse);

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testIsHandlingExceptions()
    {
        $request = Request::create('/foo/bar', 'GET');

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willThrow(new \Exception());

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ExceptionEvent::class), ExceptionEvent::NAME)->shouldBeCalled();

        $app = new Application();
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
        $exception = new \Exception();
        $expectedResponse = new JsonResponse();
        $expectedResponse->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $expectedResponse->setData(['error' => ['message' => 'Internal error', 'code' => 500]]);

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willThrow($exception);

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ExceptionEvent::class), ExceptionEvent::NAME)->shouldBeCalled();

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);

        $this->assertEquals($expectedResponse, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testIsHandlingExceptionsInDebug()
    {
        $request = Request::create('/foo/bar', 'GET');
        $exception = new \Exception();

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willThrow($exception);

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ExceptionEvent::class), ExceptionEvent::NAME)->shouldBeCalled();

        $app = new Application();
        $app['debug'] = true;
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

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willThrow($exception);

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ExceptionEvent::class), ExceptionEvent::NAME)->shouldBeCalled();

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $response = $app->handle($request);

        $this->assertEquals('{"error":{"message":"foobar","code":1001}}', $response->getContent());
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testIsThrowingExceptions()
    {
        $this->expectException(\Exception::class);

        $request = Request::create('/foo/bar', 'GET');
        $expectedResponse = new Response();
        $expectedResponse->setStatusCode(500);

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willThrow(new \Exception());

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();

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

        $routeDispatcher = $this->prophesize(Dispatcher::class);
        $routeDispatcher->handle($request)->willReturn($expectedResponse);

        $eventDispatcher = $this->prophesize(EventDispatcher::class);
        $eventDispatcher->dispatch(Argument::type(RequestEvent::class), RequestEvent::NAME)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ResponseEvent::class), ResponseEvent::NAME)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(PostResponseEvent::class), PostResponseEvent::NAME)->shouldBeCalled();

        $app = new Application();
        $app['route.dispatcher'] = $routeDispatcher->reveal();
        $app['event.dispatcher'] = $eventDispatcher->reveal();
        $app->run($request);
        $this->expectOutputString('barfoo');
    }
}
