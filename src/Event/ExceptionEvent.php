<?php

declare(strict_types=1);

namespace Linio\Tortilla\Event;

use Symfony\Component\HttpFoundation\Request;

class ExceptionEvent extends RequestEvent
{
    public const NAME = 'application.exception';

    /**
     * @var \Throwable
     */
    protected $exception;

    public function __construct(\Throwable $exception, Request $request)
    {
        parent::__construct($request);
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
