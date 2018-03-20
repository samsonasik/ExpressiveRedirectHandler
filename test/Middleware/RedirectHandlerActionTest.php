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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

class RedirectHandlerActionTest extends TestCase
{
    /** @var RedirectHandlerAction */
    protected $middleware;

    /** @var RouterInterface */
    protected $router;

    public function setUp()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
        ];
        $this->router = $this->prophesize(RouterInterface::class);
        $request      = new ServerRequest(['/']);
        $request      = $request->withUri(new Uri($config['default_url']));

        $routeHome = RouteResult::fromRoute(
            new Route('/', $this->prophesize(MiddlewareInterface::class)->reveal())
        );
        $this->router->match($request)->willReturn($routeHome);

        $this->middleware = new RedirectHandlerAction(
            $config,
            $this->router->reveal()
        );
    }

    public function testInvokeWithResponse200()
    {
        $request  = new ServerRequest(['/']);
        $response = new Response();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $response = $this->middleware->process(
            $request,
            $handler->reveal()
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testInvokeWithResponseWithDisabledHeaderHandler200()
    {
        $request  = new ServerRequest(['/']);
        $response = new Response();

        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'header_handler' => [
                'enable' => false,
                'headers' => [
                    401 => '/login',
                    503 => '/maintenance',
                ],
            ],
        ];

        $this->middleware = new RedirectHandlerAction(
            $config,
            $this->router->reveal()
        );

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $response = $this->middleware->process(
            $request,
            $handler->reveal()
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testInvokeWithResponseWithEnabledHeaderHandler()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'header_handler' => [
                'enable' => true,
                'headers' => [
                    401 => '/login',
                    503 => '/maintenance',
                ],
            ],
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $uri = $this->prophesize(Uri::class);
        $uri->getPath()->willReturn('/foo')->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $request->withUri(Argument::type(Uri::class))->willReturn($request);
        $request->getUri()->willReturn($uri);

        $routeResult = RouteResult::fromRoute(new Route('/foo', $this->prophesize(MiddlewareInterface::class)->reveal()));
        $router->match($request)->willReturn($routeResult);

        $response = new Response();
        $response = $response->withStatus(401);

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/login', $response->getHeaders()['location'][0]);
    }

    /**
     *  @expectedException \InvalidArgumentException
     *  @expectedExceptionMessage redirect value for 503 must be a string
     */
    public function testInvokeWithResponseWithEnabledHeaderHandlerButRedirectIsNotString()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'header_handler' => [
                'enable' => true,
                'headers' => [
                    402 => '/checkout',
                    401 => '/login',
                    503 => new \stdClass(),
                ],
            ],
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $response = new Response();
        $response = $response->withStatus(401);
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );
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
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testInvokeRedirectResponseWithExcludeUrlsOptions()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'options' => [
                'exclude_urls' => [
                    'https://www.github.com/samsonasik/ExpressiveRedirectHandler',
                ],
            ],
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $response = new RedirectResponse('https://www.github.com/samsonasik/ExpressiveRedirectHandler');
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testInvokeRedirectResponseWithExcludeHostsOptions()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'options' => [
                'exclude_hosts' => [
                    'www.github.com',
                ],
            ],
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $response = new RedirectResponse('https://www.github.com/samsonasik/ExpressiveRedirectHandler');
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testInvokeRedirectResponseDisallowNotRoutedUrlAndRouteMatchIsFailure()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/default',
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $uri = $this->prophesize(Uri::class);
        $uri->getPath()->willReturn('/')->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $request->withUri(Argument::type(Uri::class))->willReturn($request);

        $routeResult = RouteResult::fromRouteFailure(null);
        $router->match($request)->willReturn($routeResult);

        $response = new RedirectResponse('/foo?query');
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    public function provideInvokeRedirectResponseDisallowNotRoutedUrlAndRouteMatchIsSuccess()
    {
        return [
            ['/', true],
            ['/foo', false],
        ];
    }

    /**
     * @dataProvider provideInvokeRedirectResponseDisallowNotRoutedUrlAndRouteMatchIsSuccess
     */
    public function testInvokeRedirectResponseDisallowNotRoutedUrlAndRouteMatchIsSuccess($path, $isNotRedirected)
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
        ];
        $router = $this->prophesize(RouterInterface::class);

        $middleware = new RedirectHandlerAction(
            $config,
            $router->reveal()
        );

        $request  = $this->prophesize(ServerRequest::class);
        $uri = $this->prophesize(Uri::class);
        $uri->getPath()->willReturn($path)->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $request->withUri(Argument::type(Uri::class))->willReturn($request);
        $request->getUri()->willReturn($uri);

        $routeResult = RouteResult::fromRoute(new Route('/foo', $this->prophesize(MiddlewareInterface::class)->reveal()));
        $router->match($request)->willReturn($routeResult);

        $response = new RedirectResponse('/foo');
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $response = $middleware->process(
            $request->reveal(),
            $handler->reveal()
        );

        if ($isNotRedirected) {
            $this->assertInstanceOf(Response::class, $response);
        } else {
            $this->assertInstanceOf(RedirectResponse::class, $response);
        }
    }

    public function testInvokeRedirectResponseToSameUri()
    {
        $request  = $this->prophesize(ServerRequest::class);
        $response = new RedirectResponse('/');

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($response);

        $routeResult = RouteResult::fromRoute(new Route('/', $this->prophesize(MiddlewareInterface::class)->reveal()));

        $uri = $this->prophesize(Uri::class);
        $uri->getPath()->willReturn('/')->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $uri->getPath()->willReturn('/')->shouldBeCalled();
        $request->withUri(Argument::type(Uri::class))->willReturn($request)->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $this->router->match($request)
                     ->willReturn($routeResult)->shouldBeCalled();

        $response = $this->middleware->process(
            $request->reveal(),
            $handler->reveal()
        );
    }
}
