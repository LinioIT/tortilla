<?php

declare(strict_types=1);

namespace Linio\Tortilla\Route;

use Linio\Tortilla\Route\ControllerResolver\ControllerResolverInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DispatcherTest extends TestCase
{
    use ProphecyTrait;

    public function testIsDetectingNonExistingRoute()
    {
        $this->expectException(NotFoundHttpException::class);

        $request = Request::create('/bar', 'GET');
        $dispatcher = new Dispatcher([
            [
                'GET' => [
                    '/foo' => 'fooHandler',
                ],
            ],
            [],
        ]);

        $dispatcher->handle($request);
    }

    public function testIsDetectingMethodNotAllowed()
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $request = Request::create('/foo', 'POST');
        $dispatcher = new Dispatcher([
            [
                'GET' => [
                    '/foo' => 'fooHandler',
                ],
            ],
            [],
        ]);

        $dispatcher->handle($request);
    }

    public function testIsDetectingValidRoute()
    {
        $request = Request::create('/foo', 'GET');
        $dispatcher = new Dispatcher([
            [
                'GET' => [
                    '/foo' => 'fooHandler',
                ],
            ],
            [],
        ]);

        $expectedResponse = new Response();

        $controllerResolver = $this->prophesize(ControllerResolverInterface::class);
        $controllerResolver->getController('fooHandler')->willReturn(function (Request $request) use ($expectedResponse) {
            return $expectedResponse;
        });

        $dispatcher->setControllerResolver($controllerResolver->reveal());
        $response = $dispatcher->handle($request);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testIsDetectingValidRouteWithParams()
    {
        $request = Request::create('/foo/bar/42', 'GET');
        $dispatcher = new Dispatcher([
            [],
            [
                'GET' => [
                    [
                        'regex' => '~^(?|/foo/([^/]+)/([0-9]+))$~',
                        'routeMap' => [
                            3 => ['fooHandler', ['name' => 'name', 'id' => 'id']],
                        ],
                    ],
                ],
            ],
        ]);

        $expectedResponse = new Response();

        $controllerResolver = $this->prophesize(ControllerResolverInterface::class);
        $controllerResolver->getController('fooHandler')->willReturn(function (Request $request, $name, $id) use ($expectedResponse) {
            $this->assertEquals('bar', $name);
            $this->assertEquals(42, $id);

            return $expectedResponse;
        });

        $dispatcher->setControllerResolver($controllerResolver->reveal());
        $response = $dispatcher->handle($request);
        $this->assertEquals($expectedResponse, $response);
    }
}
