<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller\Http;

use App\Controller\AbstractController;
use App\Controller\Ws\WebSocketController;
use Carbon\Carbon;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;

/**
 * Class IndexController
 * @package App\Controller
 * @Controller(prefix="index")
 */
class IndexController extends AbstractController
{

    private $colors = ['aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'pink', 'red', 'green', 'orange', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue'];

    /**
     * @param string $message
     * @return string
     */
    private function makeText (string $message, string $author = '')
    {
        $res = [
            'text' => $message,
            'color' => collect($this->colors)->random(1)->toArray()[0],
            'time' => Carbon::now()->toDateTimeString(),
            'author' => $author,
        ];
        return Json::encode($res);
    }

    /**
     * @RequestMapping(path="login",methods="GET")
     */
    public function login ()
    {
//        $io= app()->get(SocketIO::class);
        $io = ApplicationContext::getContainer()->get(SocketIO::class);
        $io->emit('broadcast', $this->makeText('我开始广播了,今天下午不上学' . count($io->getAdapter()
                ->clients())));

        return $io->getAdapter()
            ->clients();
    }

    /**
     * @RequestMapping(path="send",methods="GET")
     */
    public function emit ()
    {
        $message = $this->request->input("message", 'fskdfjskdjflsa');
        $fd = $this->request->input("fd", 1);
        $io = app()->get(SocketIO::class);
        $socketId = app()->get(SidProviderInterface::class)->getSid((int)$fd);
        echo $socketId . PHP_EOL;
        echo $message . PHP_EOL;
        return $io->to($socketId)->emit('message', $this->makeText($message, 'system'));

    }

    /**
     * @RequestMapping(path="disconnect",methods="GET")
     */
    public function disconnect ()
    {
        $fd = $this->request->input("fd", 1);
        $io = app()->get(SocketIO::class);
        $socketId = app()->get(SidProviderInterface::class)->getSid((int)$fd);
        echo $socketId . PHP_EOL;

        $io->getAdapter()->del($socketId);

        return $io->getAdapter()
            ->clients();

    }

    /**
     * @RequestMapping(path="test",methods="GET")
     */
    public function test ()
    {
        $instance = ApplicationContext::getContainer()->get(WebSocketController::class);
    }

}
