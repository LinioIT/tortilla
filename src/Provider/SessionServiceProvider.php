<?php

declare(strict_types=1);

namespace Linio\Tortilla\Provider;

use Linio\Tortilla\ApplicationEvents;
use Linio\Tortilla\Listener\SessionListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['session'] = function ($app) {
            return new Session($app['session.storage']);
        };

        $app['session.storage'] = function ($app) {
            return $app['session.storage.native'];
        };

        $app['session.storage.handler'] = function ($app) {
            return new NativeFileSessionHandler($app['session.storage.save_path']);
        };

        $app['session.storage.native'] = function ($app) {
            return new NativeSessionStorage(
                $app['session.storage.options'],
                $app['session.storage.handler']
            );
        };

        $app['session.listener'] = function ($app) {
            $listener = new SessionListener();
            $listener->setSession($app['session']);

            return $listener;
        };

        $app['session.storage.options'] = [];
        $app['session.storage.save_path'] = null;

        $app->extend('event.dispatcher', function (EventDispatcher $eventDispatcher, $app) {
            $eventDispatcher->addListener(ApplicationEvents::REQUEST, [$app['session.listener'], 'onRequest']);
            $eventDispatcher->addListener(ApplicationEvents::RESPONSE, [$app['session.listener'], 'onResponse']);

            return $eventDispatcher;
        });
    }
}
