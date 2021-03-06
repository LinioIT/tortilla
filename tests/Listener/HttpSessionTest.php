<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class HttpSessionTest extends \PHPUnit\Framework\TestCase
{
    public function testIsSettingUpSession()
    {
        $session = new Session();
        $request = new Request();
        $event = new RequestEvent($request);
        $listener = new HttpSession();
        $listener->setSession($session);
        $listener->onRequest($event);

        $this->assertEquals($request->getSession(), $session);
    }

    public function testIsIgnoringNonExistingSession()
    {
        $request = new Request();
        $event = new RequestEvent($request);
        $listener = new HttpSession();
        $listener->onRequest($event);

        $this->assertNull($request->getSession());
    }

    public function testIsKeepingExistingSession()
    {
        $session = new Session();
        $request = new Request();
        $request->setSession($session);

        $event = new RequestEvent($request);
        $listener = new HttpSession();
        $listener->onRequest($event);

        $this->assertEquals($request->getSession(), $session);
    }

    public function testIsSavingSession()
    {
        $session = $this->prophesize(Session::class);
        $session->isStarted()->willReturn(true);
        $session->save()->shouldBeCalled();

        $request = new Request();
        $request->setSession($session->reveal());

        $event = new ResponseEvent($request, new Response());
        $listener = new HttpSession();
        $listener->onResponse($event);
    }

    public function testIsNotSavingUnstartedSession()
    {
        $session = $this->prophesize(Session::class);
        $session->isStarted()->willReturn(false);
        $session->save()->shouldNotBeCalled();

        $request = new Request();
        $request->setSession($session->reveal());

        $event = new ResponseEvent($request, new Response());
        $listener = new HttpSession();
        $listener->onResponse($event);
    }
}
