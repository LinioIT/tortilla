<?php
declare(strict_types=1);

namespace Linio\Tortilla\Route\ControllerResolver;

use Pimple\Container;

class ServiceControllerResolver implements ControllerResolverInterface
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getController($input)
    {
        if (is_callable($input)) {
            return $input;
        }

        if (strpos($input, ':') === false) {
            throw new \InvalidArgumentException('Unable to resolve provided controller: ' . $input);
        }

        list($controllerService, $method) = explode(':', $input);

        if (!isset($this->container[$controllerService])) {
            throw new \InvalidArgumentException('Unable to resolve provided controller service: ' . $controllerService);
        }

        return [$this->container[$controllerService], $method];
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
