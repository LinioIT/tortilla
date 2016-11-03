<?php

declare(strict_types=1);

namespace Linio\Tortilla\Event;

use Symfony\Component\HttpFoundation\Response;

class RequestEvent extends ApplicationEvent
{
    /**
     * @var Response
     */
    protected $response;

    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function hasResponse(): bool
    {
        return (bool) $this->response;
    }
}
