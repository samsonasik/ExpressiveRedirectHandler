<?php

namespace ExpressiveRedirectHandler\Middleware;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class RedirectHandlerActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $expressive_redirect_handler_config = (isset($config['expressive-redirect-handler']))
                                              ? $config['expressive-redirect-handler']
                                              : [];
        $router = $container->get(RouterInterface::class);

        return new RedirectHandlerAction(
            $expressive_redirect_handler_config,
            $router
        );
    }
}
