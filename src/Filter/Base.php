<?php

namespace Swooxy\Filter;


/**
 * Author:Robert
 *
 * Class Base
 * @package Swooxy\Filter
 */
abstract class Base implements FilterInterface
{

    /**
     * Author:Robert
     *
     * @return string
     */
    public function message(): string
    {
        return '对不起，您的请求操作风控限制了';
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function protocolEndTxt(): string
    {
        return "HTTP/1.1 407 Proxy Authentication Required\r\nServer: Janfish Swooxy\r\nConnection: close\r\n\r\n对不起，你的请求操作风控限制了\r\n";
    }
}