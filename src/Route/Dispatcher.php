<?php

declare(strict_types=1);

namespace Linio\Tortilla\Route;

use FastRoute\Dispatcher\GroupCountBased;
use Linio\Common\Exception\ClientException;
use Linio\Common\Exception\ExceptionTokens;
use Linio\Tortilla\Route\ControllerResolver\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Dispatcher extends GroupCountBased
{
    /**
     * @var ControllerResolverInterface
     */
    protected $controllerResolver;

    /**
     * @throws NotFoundHttpException
     * @throws ClientException
     */
    public function handle(Request $request): Response
    {
        $result = $this->dispatch($request->getMethod(), $request->getPathInfo());

        switch ($result[0]) {
            case self::NOT_FOUND:
                throw new NotFoundHttpException('Route not found: ' . $request->getPathInfo());
            case self::METHOD_NOT_ALLOWED:
                throw new ClientException(ExceptionTokens::INVALID_REQUEST, 405, 'Method not allowed: ' . $request->getMethod());
            case self::FOUND:
                $controller = $this->controllerResolver->getController($result[1]);
                $params = array_values($result[2]);
                array_unshift($params, $request);

                return call_user_func($controller, ...$params);
        }
    }

    public function setControllerResolver(ControllerResolverInterface $controllerResolver)
    {
        $this->controllerResolver = $controllerResolver;
    }
}
