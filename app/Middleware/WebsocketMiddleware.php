<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Component\Response;
use App\Service\AuthenticateService;
use Hyperf\Utils\Context;
use Phper666\JWTAuth\JWT;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class WebsocketMiddleware
 */
class WebsocketMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $baseNamespace;
    /**
     * @var Response
     */
    protected $response;

    protected $prefix = 'Bearer';

    protected $jwt;

    public function __construct (ContainerInterface $container, JWT $jwt)
    {
        $this->jwt = $jwt;
        $this->container = $container;
    }

    public function process (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $uid = $request->getQueryParams()['uid'] ?? '';
        $token = $request->getQueryParams()['token'] ?? '';
        if (!$uid || !$token) {
            // 阻止异常冒泡
            return $response->withStatus(400);
        }

        $result = make(AuthenticateService::class)->check($uid, $token);
        if (!$result) {
            // 阻止异常冒泡
            return $response->withStatus(401);
        }
        return $handler->handle($request);

    }


}