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
use Prophecy\Argument;
use Zend\Expressive\Router\RouterInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use PHPUnit\Framework\TestCase;

if (! class_exists(TestCase::class)) {
    class_alias(\PHPUnit_Framework_TestCase::class, TestCase::class);
}

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
        $this->router->match($request)
                     ->willReturn(RouteResult::fromRoute(new Route('/', 'home')));

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

    /**
     * @dataProvider provideNextForInvokeWithResponse
     */
    public function testInvokeWithResponseWithDisabledHeaderHandler($next)
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

        $response = $this->middleware->__invoke(
            $request,
            $response,
            $next
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

        $routeResult = RouteResult::fromRoute(new Route('/foo', 'foo'));
        $router->match($request)->willReturn($routeResult);

        $response = new Response();
        $response = $response->withStatus(401);
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
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
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
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
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
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
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
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
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testInvokeRedirectResponseWithExcludeDomainsOptions()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'options' => [
                'exclude_domains' => [
                    'github.com',
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
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     *  @expectedException \InvalidArgumentException
     *  @expectedExceptionMessage example.invalid is not a valid domain
     */
    public function testInvokeRedirectResponseWithExcludeDomainsOptionsWithInvalidDomain()
    {
        $config = [
            'allow_not_routed_url' => false,
            'default_url' => '/',
            'options' => [
                'exclude_domains' => [
                    'example.invalid',
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
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );
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

        $routeResult = RouteResult::fromRouteFailure();
        $router->match($request)->willReturn($routeResult);

        $response = new RedirectResponse('/foo?query');
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
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
    public function testInvokeRedirectResponseDisallowNotRoutedUrlAndRouteMatchIsSuccess($path, $isNull)
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

        $routeResult = RouteResult::fromRoute(new Route('/foo', 'foo'));
        $router->match($request)->willReturn($routeResult);

        $response = new RedirectResponse('/foo');
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        $response = $middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );

        if ($isNull) {
            $this->assertNull($response);
        } else {
            $this->assertInstanceOf(RedirectResponse::class, $response);
        }
    }

    public function testInvokeRedirectResponseToSameUri()
    {
        $request  = $this->prophesize(ServerRequest::class);
        $response = new RedirectResponse('/');
        $next = function ($req, $res, $err = null) use ($response) {
            return $response;
        };

        
        $routeResult = RouteResult::fromRoute(new Route('/', 'home'));
        $uri = $this->prophesize(Uri::class);
        $uri->getPath()->willReturn('/')->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $uri->getPath()->willReturn('/')->shouldBeCalled();
        $request->withUri(Argument::type(Uri::class))->willReturn($request)->shouldBeCalled();
        $request->getUri()->willReturn($uri)->shouldBeCalled();

        $this->router->match($request)
                     ->willReturn($routeResult)->shouldBeCalled();

        $response = $this->middleware->__invoke(
            $request->reveal(),
            $response,
            $next
        );
    }
}
