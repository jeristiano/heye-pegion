<?php


namespace App\Service;


use Hyperf\Di\Annotation\Inject;

/**
 * Class AuthenticateService
 * @package App\Service
 */
class AuthenticateService
{

    /**
     * @Inject
     * @var \App\Component\Http
     */
    protected $http;

    /**
     * @param $uid
     * @param $token
     */
    public function check ($uid, $token)
    {
        $response = $this->http->post(env('USER_CENTER'), ['uid' => $uid, 'token' => $token]);
        return $response['result'] ?? false;
    }
}