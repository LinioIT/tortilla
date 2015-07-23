<?php

namespace Linio\Tortilla;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use Linio\Exception\ErrorException;
use Linio\Exception\HttpException;
use Pimple\Container;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Linio\Tortilla\Listener\JsonBodyListener;
use Linio\Tortilla\Route\ControllerResolver\ServiceControllerResolver;
use Linio\Tortilla\Route\Dispatcher;
use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ExceptionEvent;

class Application extends Container implements HttpKernelInterface, TerminableInterface
{
    /**
     * @param array $values The parameters or objects.
     */
    public function __construct(array $values = [])
    {
        $this['debug'] = false;

        $this['event.dispatcher'] = function () {
            return new EventDispatcher();
        };

        $this['application.json_body_listener'] = function () {
            return new JsonBodyListener();
        };

        $this['controller.resolver'] = function () {
            return new ServiceControllerResolver($this);
        };

        $this['route.dispatcher'] = function () {
            $dispatcher = new Dispatcher($this['route.collector']->getData());
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
            $eventDispatcher->addListener(ApplicationEvents::REQUEST, [$this['application.json_body_listener'], 'onRequest']);

            return $eventDispatcher;
        });
    }

    /**
     * Maps a GET request to handler.
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function get($pattern, $handler)
    {
        $this['route.collector']->addRoute('GET', $pattern, $handler);
    }

    /**
     * Maps a POST request to handler.
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function post($pattern, $handler)
    {
        $this['route.collector']->addRoute('POST', $pattern, $handler);
    }

    /**
     * Maps a PUT request to handler.
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function put($pattern, $handler)
    {
        $this['route.collector']->addRoute('PUT', $pattern, $handler);
    }

    /**
     * Maps a DELETE request to handler.
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function delete($pattern, $handler)
    {
        $this['route.collector']->addRoute('DELETE', $pattern, $handler);
    }

    /**
     * Maps a PATCH request to handler.
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function patch($pattern, $handler)
    {
        $this['route.collector']->addRoute('PATCH', $pattern, $handler);
    }

    /**
     * Maps a OPTIONS request to handler.
     *
     * @param string $pattern
     * @param mixed  $handler
     */
    public function options($pattern, $handler)
    {
        $this['route.collector']->addRoute('OPTIONS', $pattern, $handler);
    }

    /**
     * Maps various HTTP requests to handler.
     *
     * @param array  $methods
     * @param string $pattern
     * @param mixed  $handler
     */
    public function match(array $methods, $pattern, $handler)
    {
        $this['route.collector']->addRoute($methods, $pattern, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $event = new RequestEvent($request);
        $this['event.dispatcher']->dispatch(ApplicationEvents::REQUEST, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        try {
            return $this['route.dispatcher']->handle($request);
        } catch (\Exception $exception) {
            if ($catch === false) {
                throw $exception;
            }

            return $this->handleException($exception, $request);
        }
    }

    /**
     * @param \Exception $exception
     * @param Request    $request
     *
     * @return Response
     */
    protected function handleException(\Exception $exception, Request $request)
    {
        $event = new ExceptionEvent($exception, $request);
        $this['event.dispatcher']->dispatch(ApplicationEvents::EXCEPTION, $event);

        if (isset($this['logger'])) {
            $this['logger']->log(($exception instanceof ErrorException) ? $exception->getLogLevel() : LogLevel::ALERT, (string) $exception);
        }

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        if ($this['debug']) {
            return new Response((string) $exception, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = new JsonResponse();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setData(['type' => '#1000', 'code' => 1000, 'title' => 'Internal error', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR]);

        if ($exception instanceof HttpException) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            $response->setData($exception->getApiProblem()->asArray());
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response)
    {
        $this['event.dispatcher']->dispatch(ApplicationEvents::TERMINATE, new Event\PostResponseEvent($request, $response));
    }

    /**
     * @param Request|null $request
     */
    public function run(Request $request = null)
    {
        if ($request === null) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $this['event.dispatcher']->dispatch(ApplicationEvents::RESPONSE, new Event\ResponseEvent($request, $response));
        $response->send();
        $this->terminate($request, $response);
    }
}
