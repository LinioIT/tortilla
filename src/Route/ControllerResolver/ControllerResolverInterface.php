<?php

namespace Linio\Tortilla\Route\ControllerResolver;

interface ControllerResolverInterface
{
    /**
     * @param mixed $input
     *
     * @throws InvalidArgumentException If unable to resolve controller
     *
     * @return callable
     */
    public function getController($input);
}
