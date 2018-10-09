<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;

class JsonRequestBodyTest extends \PHPUnit\Framework\TestCase
{
    public function testIsConvertingJsonBodyToArray()
    {
        $request = new Request([], [], [], [], [], [], '{"foo": "bar"}');
        $request->headers->set('Content-Type', 'application/json');
        $event = new RequestEvent($request);

        $listener = new JsonRequestBody();
        $listener->onRequest($event);

        $this->assertEquals('bar', $request->get('foo'));
    }

    public function testIsIgnoringNonJsonBody()
    {
        $request = new Request([], [], [], [], [], [], '<xml>');
        $request->headers->set('Content-Type', 'application/xml');
        $event = new RequestEvent($request);

        $listener = new JsonRequestBody();
        $this->assertNull($listener->onRequest($event));
    }

    public function testIsIgnoringEmptyJsonBody()
    {
        $request = new Request([], [], [], [], [], [], '');
        $request->headers->set('Content-Type', 'application/json');
        $event = new RequestEvent($request);

        $listener = new JsonRequestBody();
        $this->assertNull($listener->onRequest($event));
    }

    public function testIsDetectingBadJson()
    {
        $this->expectException(\Linio\Exception\BadRequestHttpException::class);

        $request = new Request([], [], [], [], [], [], '{"foo" "bar"}');
        $request->headers->set('Content-Type', 'application/json');
        $event = new RequestEvent($request);

        $listener = new JsonRequestBody();
        $listener->onRequest($event);
    }
}
