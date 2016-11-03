<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Tortilla\Event\RequestEvent;
use Linio\Tortilla\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Linio\Component\Microlog\Log;

class RequestResponseLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsLoggingRequest()
    {
        $request = new Request();
        $event = new RequestEvent($request);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug('Request received.', ['request' => (string) $request, 'requestId' => 'foobar'])->shouldBeCalled();
        Log::addGlobalContext('requestId', 'foobar');
        Log::setLoggerForChannel($logger->reveal(), Log::DEFAULT_CHANNEL);

        $listener = new RequestResponseLogger();
        $listener->onRequest($event);
    }

    public function testIsLoggingResponse()
    {
        $request = new Request();
        $response = new Response();
        $event = new ResponseEvent($request, $response);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug('Response sent.', ['response' => (string) $response, 'requestId' => 'foobar'])->shouldBeCalled();
        Log::addGlobalContext('requestId', 'foobar');
        Log::setLoggerForChannel($logger->reveal(), Log::DEFAULT_CHANNEL);

        $listener = new RequestResponseLogger();
        $listener->onResponse($event);
    }
}
