<?php

namespace Linio\Tortilla\Event;

use Symfony\Component\HttpFoundation\Response;

class RequestEvent extends ApplicationEvent
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return (bool) $this->response;
    }
}
