<?php

namespace ExpressiveRedirectHandlerTest\Middleware;

use ExpressiveRedirectHandler\Middleware\RedirectHandlerAction;
use ExpressiveRedirectHandler\Middleware\RedirectHandlerActionFactory;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;

class RedirectHandlerActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerInterface */
    protected $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $router = $this->prophesize(RouterInterface::class);
    }

    public function testFactory()
    {
        $factory = new RedirectHandlerActionFactory();
        $this->container->get('config')->willReturn([
            'expressive-redirect-handler' => [
                'allow_not_routed_url' => false,
                'default_url' => '/',
            ],
        ]);
        $this->container->get(RouterInterface::class)
                        ->willReturn($router);

        $this->assertTrue($factory instanceof RedirectHandlerActionFactory);
        $homePage = $factory($this->container->reveal());
        $this->assertTrue($homePage instanceof RedirectHandlerAction);
    }
}
