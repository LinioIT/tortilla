<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsHandlerTest extends TestCase
{
    public function testIsRespondingToPreflightedRequests()
    {
        $event = new RequestEvent(Request::create('/api/test', 'OPTIONS'));

        $handleCors = new CorsHandler('*', 'GET, POST', 'Authorization');
        $handleCors->onRequest($event);

        $this->assertEquals('*', $event->getResponse()->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, POST', $event->getResponse()->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals('Authorization', $event->getResponse()->headers->get('Access-Control-Allow-Headers'));
    }

    public function testIsDecoratingResponseHeaders()
    {
        $event = new ResponseEvent(Request::create('/api/test', 'POST'), new Response());

        $handleCors = new CorsHandler('*', 'GET, POST', 'Authorization');
        $handleCors->onResponse($event);

        $this->assertEquals('*', $event->getResponse()->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, POST', $event->getResponse()->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals('Authorization', $event->getResponse()->headers->get('Access-Control-Allow-Headers'));
    }
}
