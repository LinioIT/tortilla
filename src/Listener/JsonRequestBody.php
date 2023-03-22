<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Common\Exception\ClientException;
use Linio\Common\Exception\ExceptionTokens;
use Linio\Component\Util\Json;
use Linio\Tortilla\Event\RequestEvent;

class JsonRequestBody
{
    /**
     * @throws ClientException
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (strpos($request->headers->get('Content-Type', ''), 'application/json') !== 0) {
            return;
        }

        if (!($content = $request->getContent())) {
            return;
        }

        try {
            $data = Json::decode($content);
        } catch (\Exception $exception) {
            throw new ClientException(ExceptionTokens::INVALID_REQUEST, 400, 'Invalid JSON.');
        }

        $request->request->replace(is_array($data) ? $data : []);
    }
}
