<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;


use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

use App\Service\JwtService;


class AuthMiddleware implements MiddlewareInterface
{
	/**
	 * @Inject
     * @var HttpResponse $response
     */
    protected $response;

    /**
     * @Inject
     * @var JwtService $jwt
     */
    protected $jwt;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
    	if(!$this->jwt->checkToken()){
	    	return $this->response->json(
	            [
	                'code' => -1,
	                'data' => [
	                    'error' => '请先登录.',
	                ],
	            ]
	        );
	    }else{
	    	return $handler->handle($request);
	    }
    }

}
