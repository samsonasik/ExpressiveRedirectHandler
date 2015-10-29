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

namespace ExpressiveRedirectHandler\Middleware;

use InvalidArgumentException;
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

    public function __invoke($request, $response, callable $next = null)
    {
        if (null === $next) {
            return $response;
        }

        $response = $next($request, $response);

        if ($response instanceof RedirectResponse) {
            $allow_not_routed_url = (isset($this->config['allow_not_routed_url'])) ? $this->config['allow_not_routed_url'] : false;
            $default_url          = (isset($this->config['default_url'])) ? $this->config['default_url'] : '/';

            if (true === $allow_not_routed_url) {
                return $response;
            }

            $currentPath = $request->getUri()->getPath();
            $uriTarget   = $response->getHeader('location')[0];

            $newUri        = new Uri($uriTarget);
            $request       = $request->withUri(new Uri($uriTarget));
            $uriTargetPath = $newUri->getPath();
            $match         = $this->router->match($request);

            if ($match->isFailure()
                && $uriTarget !==  $default_url
                && $uriTargetPath !== $default_url
            ) {
                return $response->withHeader('location', $default_url);
            } else {
                if ($currentPath !== $uriTargetPath) {
                    return $response;
                }
            }
        } else {
            return $response;
        }
    }
}
