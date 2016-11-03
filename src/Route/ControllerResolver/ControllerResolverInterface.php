<?php

declare(strict_types=1);

namespace Linio\Tortilla\Route\ControllerResolver;

interface ControllerResolverInterface
{
    /**
     * @throws InvalidArgumentException If unable to resolve controller
     */
    public function getController($input);
}
