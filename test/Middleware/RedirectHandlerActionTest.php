<?php

namespace ExpressiveRedirectHandlerTest\Middleware;

use ExpressiveRedirectHandler\Middleware\RedirectHandlerAction;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RedirectHandlerActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RedirectHandlerAction */
    protected $middleware;

    public function setUp()
    {
        $config = [
            'expressive-redirect-handler' => [
                'allow_not_routed_url' => false,
                'default_url' => '/',
            ],
        ];
        $this->router = $this->prophesize(RouterInterface::class);

        $this->middleware = new RedirectHandlerAction(
            $config,
            $this->router->reveal()
        );
    }

    public function testInvokeWithHtmlResponse()
    {
        $request  = new ServerRequest(['/']);
        $response = new Response();

        $response = $this->middleware->__invoke(
            $request,
            $response,
            null
        );

        $this->assertInstanceOf(Response::class, $response);
    }
}
