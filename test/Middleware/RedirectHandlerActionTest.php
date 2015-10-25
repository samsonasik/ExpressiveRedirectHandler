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
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\RouteResult;

class RedirectHandlerActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RedirectHandlerAction */
    protected $middleware;

    public function setUp()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
        ];
        $this->router = $this->prophesize(RouterInterface::class);

        $this->middleware = new RedirectHandlerAction(
            $config,
            $this->router->reveal()
        );
    }

    public function provideNextForInvokeWithResponse()
    {
        return [
            [null],
            [function ($req, $res, $err = null) { return new Response(); }]
        ];
    }

    /**
     * @dataProvider provideNextForInvokeWithResponse
     */
    public function testInvokeWithResponse($next)
    {
        $request  = new ServerRequest(['/']);
        $response = new Response();

        $response = $this->middleware->__invoke(
            $request,
            $response,
            $next
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testInvokeRedirectResponseAllowNotRoutedUrl()
    {
        $config = [
            'allow_not_routed_url' => true,
            'default_url' => '/',
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $response = new RedirectResponse('/foo');
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );
    }

    public function testInvokeRedirectResponseToSameUri()
    {
        $request  = $this->prophesize(ServerRequest::class);
        $response = new RedirectResponse('/');
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $routeResult = RouteResult::fromRouteMatch('home', function() {}, []);
        $uri = $this->prophesize(Uri::class);
        $uri->getPath()->willReturn('/')->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();
        $request->withUri(new Uri('/'))
                ->willReturn($request)
                ->shouldBeCalled();
        $this->router->match($request)
                     ->willReturn($routeResult)->shouldBeCalled();

        $response = $this->middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );

        $this->assertNull($response);
    }
}
