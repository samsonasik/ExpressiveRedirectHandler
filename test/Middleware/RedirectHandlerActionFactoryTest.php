<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ExpressiveRedirectHandlerTest\Middleware;

use ExpressiveRedirectHandler\Middleware\RedirectHandlerAction;
use ExpressiveRedirectHandler\Middleware\RedirectHandlerActionFactory;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use PHPUnit\Framework\TestCase;

if (! class_exists(TestCase::class)) {
    class_alias(\PHPUnit_Framework_TestCase::class, TestCase::class);
}

class RedirectHandlerActionFactoryTest extends TestCase
{
    /** @var ContainerInterface */
    protected $container;

    protected function setUp()
    {
        if (interface_exists(PsrContainerInterface::class)) {
            $this->container = $this->prophesize(PsrContainerInterface::class);
        } else {
            $this->container = $this->prophesize(ContainerInterface::class);
        }
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
        $router = $this->prophesize(RouterInterface::class);
        $this->container->get(RouterInterface::class)
                        ->willReturn($router);

        $this->assertTrue($factory instanceof RedirectHandlerActionFactory);
        $homePage = $factory($this->container->reveal());
        $this->assertTrue($homePage instanceof RedirectHandlerAction);
    }
}
