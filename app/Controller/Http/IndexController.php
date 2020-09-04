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

use App\Constants\MemoryTable;
use App\Controller\AbstractController;
use App\Middleware\InternalNetworkMiddleware;
use App\Request\BroadcastRequest;
use App\Request\PushRequest;
use Carbon\Carbon;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Memory\TableManager;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\Utils\Codec\Json;

/**
 * Class IndexController
 * @package App\Controller
 * @Controller(prefix="notification")
 * @Middleware(InternalNetworkMiddleware::class)
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
     * 单点/群体推送
     * @RequestMapping(path="push",methods="POST,GET")
     */
    public function push (PushRequest $request)
    {

        $users = $request->input('users');
        $message = $request->input('data');
        $io = app()->get(SocketIO::class);

        $offline = [];
        foreach ($users as $uid) {
            $socketId = TableManager::get(MemoryTable::USER_TO_FD)->get((string)$uid, 'fd');
            $this->logger->debug($socketId);
            if ($socketId) {
                $io->to($socketId)->emit('message', $this->makeText($message));
                continue;
            }
            $offline[] = $uid;
        }
        return $this->response->success($offline, 0, '已发送,其中以下用户不在线');

    }


    /**
     * @RequestMapping(path="broadcast",methods="POST")
     */
    public function broadcast (BroadcastRequest $request)
    {
        $message = $request->input("data");
        $this->logger->debug($message);
        $io = app()->get(SocketIO::class);
        $io->broadcast(true)->emit('broadcast', $this->makeText($message, 'system'));
        return $this->response->success(null, 0, '已广播');
    }


    /**
     * @RequestMapping(path="clients",methods="GET")
     */
    public function clients ()
    {

        $io = app()->get(SocketIO::class);
        $rep['online_num']=count($io->getAdapter()->clients());
        return $this->response->success($rep);
    }

}
