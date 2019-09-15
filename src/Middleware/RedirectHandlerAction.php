<?php

declare(strict_types=1);

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

namespace ExpressiveRedirectHandler\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;
use Zend\Expressive\Router\RouterInterface;

class RedirectHandlerAction implements MiddlewareInterface
{
    private $config;

    private $router;

    public function __construct(array $config, RouterInterface $router)
    {
        $this->config = $config;
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);

        if (isset($this->config['header_handler']['enable'])
            && $this->config['header_handler']['enable'] === true
            && ! empty($this->config['header_handler']['headers'])
        ) {
            $statusCode = $response->getStatusCode();
            foreach ($this->config['header_handler']['headers'] as $code => $redirect) {
                if (! \is_string($redirect)) {
                    throw new InvalidArgumentException(\sprintf(
                        'redirect value for %s must be a string',
                        $code
                    ));
                }
                if ($code === $statusCode) {
                    $response = new RedirectResponse($redirect);
                }
            }
        }

        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        $allow_not_routed_url = $this->config['allow_not_routed_url'] ?? false;
        $exclude_urls         = $this->config['options']['exclude_urls'] ?? [];
        $exclude_hosts        = $this->config['options']['exclude_hosts'] ?? [];

        $uriTarget     = $response->getHeader('location')[0];
        $uriTargetHost = (new Uri($uriTarget))->getHost();

        if (true === $allow_not_routed_url ||
            \in_array($uriTarget, $exclude_urls) ||
            \in_array($uriTargetHost, $exclude_hosts)
        ) {
            return $response;
        }

        $default_url = $this->config['default_url'] ?? '/';
        $currentPath = $request->getUri()->getPath();

        $newUri        = new Uri($uriTarget);
        $request       = $request->withUri($newUri);
        $uriTargetPath = $newUri->getPath();
        $match         = $this->router->match($request);

        if ($match->isFailure()
            || ($match->isSuccess()
                    && $uriTargetPath === $currentPath
                    && $uriTarget !== $default_url
                    && $uriTargetPath !== $default_url
                )
        ) {
            return $response->withHeader('location', $default_url);
        }

        if ($uriTarget === $default_url || $uriTargetPath === $default_url) {
            return new Response();
        }

        return $response;
    }
}
