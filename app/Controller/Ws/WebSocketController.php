<?php


namespace App\Controller\Ws;


use Carbon\Carbon;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;

/**
 * @SocketIONamespace("/")
 * Class WebSocketController
 * @package App\Controller\Ws
 */
class WebSocketController extends BaseNamespace
{

    private $colors = ['aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'pink', 'red', 'green', 'orange', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue'];

    /**
     * @param string $message
     * @return string
     */
    private function makeText (string $message, string $author = 'system')
    {
        $res = [
            'text' => $message,
            'author' => $author,
            'color' => collect($this->colors)->random(1)->toArray()[0],
            'time' => Carbon::now()->toDateTimeString(),
        ];
        return Json::encode($res);
    }

    /**
     * @Event("event")
     * @param   $socket
     * @param   $data
     */
    public function onEvent (Socket $socket, $data)
    {
        // 应答
        return 'Event Received: ' . $data;
    }


    /**
     * @Event("join-room")
     * @param string $data
     */
    public function onJoinRoom (Socket $socket, $data)
    {
        // 将当前用户加入房间
        $socket->join($data);
        // 向房间内其他用户推送（不含当前用户）
        $socket->to($data)->emit('event', $socket->getSid() . "has joined {$data}");

        // 向房间内所有人广播（含当前用户）
        $es = $this->makeText('There are ' . count($socket->getAdapter()->clients($data)) . " players in {$data}");
        $this->emit('event', $es);

    }


    /**
     * @param \Hyperf\SocketIOServer\Socket $socket
     */
    private function closeSocket (Socket $socket)
    {
        $es = $this->makeText($socket->getSid() . '已经离开了,There are ' . count($socket->getAdapter()->clients()) . " players now");
        $this->broadcast->emit('broadcast', $es);
    }

    /**
     * @Event("message")
     * @param string $data
     */
    public function onMessage (Socket $socket, $data)
    {
        // 向房间内所有人广播（含当前用户）
        $es = $this->makeText("{$data}", $socket->getSid());
        $this->emit('message', $es);
    }

    /**
     * @Event("disconnect")
     * @param string $data
     */
    public function onDisconnect (Socket $socket)
    {
        //先删除这个离开的链接,在向全体推送
        $this->adapter->del($socket->getSid());
        $es = $this->makeText($socket->getSid() . '已经离开了,There are ' . count($socket->getAdapter()->clients()) . " players now");
        $this->emit('broadcast', $es);
    }


}