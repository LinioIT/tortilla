<?php

declare(strict_types=1);

namespace Linio\Tortilla;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use Linio\Common\Exception\DomainException;
use Linio\Component\Microlog\Log;
use Linio\Tortilla\Event\ExceptionEvent;
use Linio\Tortilla\Event\PostResponseEvent;
use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Linio\Tortilla\Listener\JsonRequestBody;
use Linio\Tortilla\Route\ControllerResolver\ServiceControllerResolver;
use Linio\Tortilla\Route\Dispatcher;
use Pimple\Container;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class Application extends Container implements HttpKernelInterface, TerminableInterface
{
    /**
     * @param array $values The parameters or objects
     */
    public function __construct(array $values = [])
    {
        $this['debug'] = false;
        $this['config'] = [];

        $this['event.dispatcher'] = function () {
            return new EventDispatcher();
        };

        $this['application.json_request_body'] = function () {
            return new JsonRequestBody();
        };

        $this['controller.resolver'] = function () {
            return new ServiceControllerResolver($this);
        };

        $this['route.dispatcher'] = function () {
            $dispatcher = new Dispatcher($this->getRouteCache());
            $dispatcher->setControllerResolver($this['controller.resolver']);

            return $dispatcher;
        };

        $this['route.parser'] = function () {
            return new RouteParser();
        };

        $this['route.data_generator'] = function () {
            return new DataGenerator();
        };

        $this['route.collector'] = function () {
            return new RouteCollector($this['route.parser'], $this['route.data_generator']);
        };

        parent::__construct($values);

        $this->extend('event.dispatcher', function (EventDispatcher $eventDispatcher) {
            $eventDispatcher->addListener(RequestEvent::NAME, [$this['application.json_request_body'], 'onRequest']);

            return $eventDispatcher;
        });
    }

    public function getRouteCache(): array
    {
        $routeCache = $this['config']['route_cache'] ?? false;

        if (!$this['debug'] && $routeCache && file_exists($routeCache)) {
            return require $routeCache;
        }

        $routeData = $this['route.collector']->getData();

        if (!$this['debug'] && $routeCache) {
            file_put_contents($routeCache, '<?php return ' . var_export($routeData, true) . ';');
        }

        return $routeData;
    }

    /**
     * Maps a GET request to handler.
     */
    public function get(string $pattern, $handler)
    {
        $this['route.collector']->addRoute('GET', $pattern, $handler);
    }

    /**
     * Maps a POST request to handler.
     */
    public function post(string $pattern, $handler)
    {
        $this['route.collector']->addRoute('POST', $pattern, $handler);
    }

    /**
     * Maps a PUT request to handler.
     */
    public function put(string $pattern, $handler)
    {
        $this['route.collector']->addRoute('PUT', $pattern, $handler);
    }

    /**
     * Maps a DELETE request to handler.
     */
    public function delete(string $pattern, $handler)
    {
        $this['route.collector']->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * Maps a PATCH request to handler.
     */
    public function patch(string $pattern, $handler)
    {
        $this['route.collector']->addRoute('PATCH', $pattern, $handler);
    }

    /**
     * Maps a OPTIONS request to handler.
     */
    public function options(string $pattern, $handler)
    {
        $this['route.collector']->addRoute('OPTIONS', $pattern, $handler);
    }

    /**
     * Maps various HTTP requests to handler.
     */
    public function match(array $methods, string $pattern, $handler)
    {
        $this['route.collector']->addRoute($methods, $pattern, $handler);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response
    {
        try {
            $event = new RequestEvent($request);
            $this['event.dispatcher']->dispatch($event, RequestEvent::NAME);

            if ($event->hasResponse()) {
                return $event->getResponse();
            }

            return $this['route.dispatcher']->handle($request);
        } catch (\Throwable $exception) {
            if ($catch === false) {
                throw $exception;
            }

            return $this->handleErrors($exception, $request);
        }
    }

    protected function handleErrors(\Throwable $exception, Request $request): Response
    {
        $event = new ExceptionEvent($exception, $request);
        $this['event.dispatcher']->dispatch($event, ExceptionEvent::NAME);
        Log::log($exception->getMessage(), LogLevel::ALERT);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        if ($this['debug']) {
            return new Response((string) $exception, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setData(['error' => ['message' => 'Internal error', 'code' => 500]]);

        if ($exception instanceof DomainException) {
            $response->setStatusCode($exception->getCode());
            $response->setData(['error' => ['message' => $exception->getMessage(), 'code' => $exception->getCode()]]);
        }

        return $response;
    }

    public function terminate(Request $request, Response $response)
    {
        $this['event.dispatcher']->dispatch(new PostResponseEvent($request, $response), PostResponseEvent::NAME);
    }

    public function run(Request $request = null)
    {
        if ($request === null) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $this['event.dispatcher']->dispatch(new ResponseEvent($request, $response), ResponseEvent::NAME);
        $response->send();
        $this->terminate($request, $response);
    }
}
