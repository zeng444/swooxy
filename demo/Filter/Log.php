<?php

use Swooxy\Filter\Base;
use Swooxy\Protocol\Http;

/**
 * Author:Robert
 *
 * Class Log
 */
class Log extends Base
{
    /**
     * Author:Robert
     *
     * @param Http $http
     * @return bool
     */
    public function run(Http $http): bool
    {
        $data = json_encode([
            'method' => $http->getMethod(),
            'host' => $http->getHost(),
            'port' => $http->getPort(),
            'url' => $http->getUrl(),
            'header' => $http->getHeaders(),
            'body' => $http->getBody(),
            'isIpv6' => $http->isIpv6(),
        ]);
        echo $data.PHP_EOL;
        return true;
    }

}