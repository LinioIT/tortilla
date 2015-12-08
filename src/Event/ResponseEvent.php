<?php
declare(strict_types=1);

namespace Linio\Tortilla\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseEvent extends ApplicationEvent
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request);
        $this->response = $response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
