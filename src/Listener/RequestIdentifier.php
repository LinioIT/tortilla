<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Component\Microlog\Log;
use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;

class RequestIdentifier
{
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->headers->has('X-Request-ID')) {
            $request->headers->set('X-Request-ID', uniqid((string) mt_rand()));
        }

        Log::addGlobalContext('requestId', $request->headers->get('X-Request-ID'));
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->headers->has('X-Request-ID')) {
            $response->headers->set('X-Request-ID', $request->headers->get('X-Request-ID'));
        }
    }
}
