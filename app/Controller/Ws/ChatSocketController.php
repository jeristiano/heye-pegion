<?php


namespace App\Controller\Ws;


use Carbon\Carbon;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;

/**
 * @SocketIONamespace("/chat")
 */
class ChatSocketController extends BaseNamespace
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
        $data = Json::decode($data);
        // 将当前用户加入房间
        $socket->join($data['room_id']);
        // 向房间内其他用户推送（不含当前用户）
        $new_message = [
            'type' => $data['type'],
            'client_id' => $socket->getFd(),
            'client_name' => htmlspecialchars($data['client_name']),
            'time' => date('Y-m-d H:i:s')
        ];
        echo Json::encode($new_message);
        $this->in($data['room_id'])->emit('message', Json::encode($new_message));
    }


    /**
     * @Event("message")
     * @param string $data
     */
    public function onMessage (Socket $socket, $data)
    {
        // 向房间内所有人广播（含当前用户）
        $data = Json::decode($data);
        if ($data['to_client_id'] != 'all') {
            $new_message = [
                'type' => 'say',
                'room_id' => $data['room_id'],
                'from_client_id' => $data['from_client_id'],
                'from_client_name' => $data['from_client_name'],
                'to_client_id' => $data['to_client_id'],
                'content' => "<b>对你说: </b>" . nl2br(htmlspecialchars($data['content'])),
                'time' => date('Y-m-d H:i:s'),
            ];
            $this->in($data['room_id'])->to($data['to_client_id'])->emit('message', Json::encode($new_message));
            return;
        }


        $broadcast_message = [
            'type' => 'say',
            'from_client_id' => $socket->getFd(),
            'from_client_name' => 111,
            'to_client_id' => 'all',
            'content' => nl2br(htmlspecialchars($data['content'])),
            'time' => date('Y-m-d H:i:s'),
        ];

      return  $this->in($data['room_id'])->emit('message', Json::encode($broadcast_message));

    }

}