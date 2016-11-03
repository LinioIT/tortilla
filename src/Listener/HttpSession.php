<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Session\Session;

class HttpSession
{
    /**
     * @var Session
     */
    protected $session;

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (null === $this->session || $request->hasSession()) {
            return;
        }

        $request->setSession($this->session);
    }

    public function onResponse(ResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();

        if ($session && $session->isStarted()) {
            $session->save();
        }
    }
}
