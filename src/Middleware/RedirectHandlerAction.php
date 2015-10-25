<?php

namespace ExpressiveRedirectHandler\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Diactoros\Uri;

class RedirectHandlerAction
{
    private $config;

    private $router;

    public function __construct(array $config, RouterInterface $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $response = $next($request, $response);

        if ($response instanceof RedirectResponse) {
            $allow_not_routed_url = (isset($this->config['allow_not_routed_url'])) ? $this->config['allow_not_routed_url'] : false;
            $default_url = (isset($this->config['default_url'])) ? $this->config['default_url'] : '/';

            if (true === $allow_not_routed_url) {
                return $response;
            }

            $currentPath = $request->getUri()->getPath();
            $uri     = $response->getHeader('location')[0];
            $request = $request->withUri(new Uri($uri));
            $match   = $this->router->match($request);

            if ($match->isFailure()
                &&  $uri !==  $default_url
                && $currentPath !== $default_url
            ) {
                return $response->withHeader('location', $default_url);
            }
        }

        return $response;
    }
}
