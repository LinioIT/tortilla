<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class CorsHandler
{
    /**
     * @var string
     */
    protected $origin;

    /**
     * @var string
     */
    protected $allowedMethods;

    /**
     * @var string
     */
    protected $allowedHeaders;

    public function __construct(string $origin, string $allowedMethods, string $allowedHeaders)
    {
        $this->origin = $origin;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() == 'OPTIONS') {
            $response = new Response();
            $this->addHeaders($response);
            $event->setResponse($response);

            return;
        }
    }

    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $this->addHeaders($response);
    }

    protected function addHeaders(Response $response)
    {
        $response->headers->set('Access-Control-Allow-Origin', $this->origin);
        $response->headers->set('Access-Control-Allow-Methods', $this->allowedMethods);
        $response->headers->set('Access-Control-Allow-Headers', $this->allowedHeaders);
    }
}
