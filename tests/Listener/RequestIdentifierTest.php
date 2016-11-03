<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestIdentifierTest extends \PHPUnit_Framework_TestCase
{
    public function testIsUsingExistingRequestId()
    {
        $request = new Request();
        $request->headers->set('X-Request-ID', 'foobar');
        $event = new RequestEvent($request);

        $listener = new RequestIdentifier();
        $listener->onRequest($event);

        $this->assertEquals('foobar', $request->headers->get('X-Request-ID'));
    }

    public function testIsGeneratingRequestIdWhenMissing()
    {
        $request = new Request();
        $event = new RequestEvent($request);

        $listener = new RequestIdentifier();
        $listener->onRequest($event);

        $this->assertRegexp('/[a-zA-Z0-9]+/', $request->headers->get('X-Request-ID'));
    }

    public function testIsSendingRequestIdWithResponse()
    {
        $request = new Request();
        $request->headers->set('X-Request-ID', 'foobar');

        $response = new Response();
        $event = new ResponseEvent($request, $response);

        $listener = new RequestIdentifier();
        $listener->onResponse($event);

        $this->assertEquals('foobar', $request->headers->get('X-Request-ID'));
        $this->assertEquals('foobar', $response->headers->get('X-Request-ID'));
    }
}
