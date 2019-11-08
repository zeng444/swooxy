<?php

use Swooxy\Filter\Base;
use Swooxy\Protocol\Http;

/**
 * Author:Robert
 *
 * Class Filter
 */
class Rule extends Base
{

    /**
     * Author:Robert
     *
     * @param Http $http
     * @return bool
     */
    public function run(Http $http): bool
    {
        print_r([
            'method' => $http->getMethod(),
            'host' => $http->getHost(),
            'port' => $http->getPort(),
            'url' => $http->getUrl(),
            'header' => $http->getHeaders(),
            'body' => $http->getBody(),
            'isIpv6' => $http->isIpv6(),
        ]);
        return true;
    }

}