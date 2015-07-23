<?php

namespace Linio\Tortilla\Event;

use Symfony\Component\HttpFoundation\Request;

class ExceptionEvent extends RequestEvent
{
    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * @param \Exception $exception
     * @param Request    $request
     */
    public function __construct(\Exception $exception, Request $request)
    {
        parent::__construct($request);
        $this->exception = $exception;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
