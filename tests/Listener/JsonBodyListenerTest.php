<?php
declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Symfony\Component\HttpFoundation\Request;
use Linio\Tortilla\Event\RequestEvent;

class JsonBodyListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsConvertingJsonBodyToArray()
    {
        $request = new Request([], [], [], [], [], [], '{"foo": "bar"}');
        $request->headers->set('Content-Type', 'application/json');
        $event = new RequestEvent($request);

        $listener = new JsonBodyListener();
        $listener->onRequest($event);

        $this->assertEquals('bar', $request->get('foo'));
    }

    public function testIsIgnoringNonJsonBody()
    {
        $request = new Request([], [], [], [], [], [], '<xml>');
        $request->headers->set('Content-Type', 'application/xml');
        $event = new RequestEvent($request);

        $listener = new JsonBodyListener();
        $this->assertNull($listener->onRequest($event));
    }

    public function testIsIgnoringEmptyJsonBody()
    {
        $request = new Request([], [], [], [], [], [], '');
        $request->headers->set('Content-Type', 'application/json');
        $event = new RequestEvent($request);

        $listener = new JsonBodyListener();
        $this->assertNull($listener->onRequest($event));
    }

    /**
     * @expectedException Linio\Exception\BadRequestHttpException
     */
    public function testIsDetectingBadJson()
    {
        $request = new Request([], [], [], [], [], [], '{"foo" "bar"}');
        $request->headers->set('Content-Type', 'application/json');
        $event = new RequestEvent($request);

        $listener = new JsonBodyListener();
        $listener->onRequest($event);
    }
}
