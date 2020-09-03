<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use App\Middleware\WebsocketMiddleware;
use Hyperf\HttpServer\Router\Router;
use Hyperf\SocketIOServer\SocketIO;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\Http\HomeController@index');

//Router::addServer('socket-io', function () {
//    Router::get('/socket-io/', SocketIO::class, [
//        'middleware' => [WebsocketMiddleware::class]
//    ]);
//
//});

