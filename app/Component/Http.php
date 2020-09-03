<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */
namespace App\Component;

use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Utils\Packer\JsonPacker;

/**
 * Class Http
 * @package App\Component
 */
class Http
{
    private $packer;

    private $option;

    private $clientFactory;

    /**
     * Http constructor.
     * @param \Hyperf\Guzzle\HandlerStackFactory $clientFactory
     */
    public function __construct(HandlerStackFactory $stackFactory, JsonPacker $packer)
    {
        $this->clientFactory = $this->poolHandler($stackFactory->create());
        $this->option = ['timeout' => 3];
        $this->packer = $packer;
    }

    /**
     * @param $url
     * @param $param
     * @param array $options
     */
    public function post($url, $param, $options = []): array
    {
        $options = array_merge($this->option, $options);
        $client = $this->clientFactory->create($options);
        $form['json'] = $param;
        Log::debug('发起请求参数和地址:' . $url, $form);

        try {
            $response = $client->request('POST', $url, $form);
            $data = $this->packer->unpack($response->getBody()->getContents());
            Log::debug('请求响应数据:', $this->decorating($response, $data));
            return $data;
        } catch (GuzzleException $e) {
            $data['error_msg'] = $e->getMessage();
            $data['error_code'] = $e->getCode();
            $data['error_file'] = $e->getFile();
            Log::error('请求出现错误:', $data);
            return [];
        }
    }

    /**
     * @param $url
     * @param array $param
     * @param array $options
     */
    public function get($url, $param = [], $options = []): array
    {
        $options = array_merge($this->option, $options);
        $client = $this->clientFactory->create($options);
        $form['query'] = $param;

        Log::info('发起请求参数和地址:' . $url, $form);

        try {
            $response = $client->request('GET', $url, $form);
            $data = $this->packer->unpack($response->getBody()->getContents());
            Log::info('请求响应数据:', $this->decorating($response, $data));
            return $data;
        } catch (GuzzleException $e) {
            $data['error_msg'] = $e->getMessage();
            $data['error_code'] = $e->getCode();
            $data['error_file'] = $e->getFile();
            Log::error('请求出现错误:', $data);
            return [];
        }
    }

    private function poolHandler($stack): ClientFactory
    {
        return make(ClientFactory::class, [
            'config' => [
                'handler' => $stack,
            ],
        ]);
    }

    /**
     * @param $response
     * @param $data
     */
    private function decorating($response, $data): array
    {
        if (! $data) {
            return [];
        }
        return collect($data)->put('http_status', $response->getStatusCode())->toArray();
    }
}
