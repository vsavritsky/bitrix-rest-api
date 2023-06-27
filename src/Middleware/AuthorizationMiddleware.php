<?php
namespace BitrixRestApi\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use OAuth2;

/**
 * Slim Middleware to handle OAuth2 Authorization.
 */
class AuthorizationMiddleware extends \Chadicus\Slim\OAuth2\Middleware\Authorization implements MiddlewareInterface
{
    private $server;

    /**
     * Array of scopes required for authorization.
     *
     * @var array
     */
    private $scopes;

    /**
     * Container for token.
     *
     * @var mixed
     */
    private $container;

    public function __construct(OAuth2\Server $server, $container = new \ArrayObject(), array $scopes = [])
    {
        parent::__construct($server, $container, $scopes);

        $this->server = $server;
        $this->container = $container;
        $this->scopes = $this->formatScopes($scopes);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this($this->server, $this->container, $this->scopes);
    }

    /**
     * Helper method to ensure given scopes are formatted properly.
     *
     * @param array $scopes Scopes required for authorization.
     *
     * @return array The formatted scopes array.
     */
    private function formatScopes(array $scopes)
    {
        if (empty($scopes)) {
            return [null]; //use at least 1 null scope
        }

        array_walk(
            $scopes,
            function (&$scope) {
                if (is_array($scope)) {
                    $scope = implode(' ', $scope);
                }
            }
        );

        return $scopes;
    }
}
