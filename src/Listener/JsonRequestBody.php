<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Component\Util\Json;
use Linio\Exception\BadRequestHttpException;
use Linio\Tortilla\Event\RequestEvent;

class JsonRequestBody
{
    /**
     * @throws BadRequestHttpException
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
            return;
        }

        if (!($content = $request->getContent())) {
            return;
        }

        try {
            $data = Json::decode($content);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        $request->request->replace(is_array($data) ? $data : []);
    }
}
