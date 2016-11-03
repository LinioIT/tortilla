<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Component\Microlog\Log;
use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;

class RequestResponseLogger
{
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        Log::debug('Request received.', ['request' => (string) $request]);
    }

    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();

        Log::debug('Response sent.', ['response' => (string) $response]);
    }
}
