<?php

namespace Comoco\TracClientPhp;

use Comoco\TracClientPhp\Exception\ApiException;

class TracApiHandler
{
    protected $api_url = null;
    protected $username = null;
    protected $password = null;

    /**
     * @param string $api_url json rpc api url. Ex: http://trac.local/login/jsonrpc
     * @param string $username login username
     * @param string $password login password
     */
    public function __construct($api_url, $username, $password)
    {
        $this->api_url = $api_url;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * call trac rpc api (https://trac-hacks.org/wiki/XmlRpcPlugin)
     *
     * @param string $method api method's name
     * @param array $params api method's params
     * @return mixed $method return data
     *
     * @throws Comoco\TracClientPhp\Exception\ApiException if api response error
     */
    public function call($method, array $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "method" => $method,
            "params" => $params
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $curl_result = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status_code == 401) {
            throw new ApiException('username or password not valid');
        } elseif ($status_code != 200) {
            throw new ApiException('call rpc api fail (status code : ' . $status_code . ')');
        }

        $response = json_decode($curl_result, true);
        if (isset($response['error'])) {
            throw new ApiException($response['error']['message']);
        }
        return $response['result'];
    }
}
