<?php

declare(strict_types=1);

namespace Linio\Tortilla\Listener;

use Linio\Component\Util\Json;
use Linio\Tortilla\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonRequestBody
{
    /**
     * @throws BadRequestHttpException
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
            throw new BadRequestHttpException('Invalid JSON.');
        }

        $request->request->replace(is_array($data) ? $data : []);
    }
}
