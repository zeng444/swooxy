<?php

namespace Swooxy\Protocol;


class Http
{
    /**
     * Author:Robert
     *
     * @var
     */
    private $_data;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_method;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_url;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_protocol;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_host;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_port;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_body;

    /**
     * Author:Robert
     *
     * @var
     */
    private $_isIpv6;

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_header = [];

    /**
     * Http constructor.
     * @param $data
     */
    function __construct($data)
    {
        $this->_data = $data;
    }


    /**
     * Author:Robert
     *
     */
    private function parse()
    {
        list($header, $body) = explode("\r\n\r\n", $this->_data, 2);
        $headerData = explode("\r\n", $header);
        $protocol = $headerData[0];
        unset($headerData[0]);
        $header = [];
        foreach ($headerData as $value) {
            list($name, $value) = explode(':', $value, 2);
            $header[$name] = trim($value);
        }
        $this->_body = $body;
        $this->_protocol = $protocol;
        $this->_header = $header;
    }


    /**
     * Author:Robert
     *
     * @return string
     */
    public function getBody(): string
    {
        if ($this->_body) {
            return $this->_body;
        }
        $this->parse();
        return $this->_body;
    }

    /**
     * Author:Robert
     *
     * @return array
     */
    public function getHeaders(): array
    {
        if ($this->_header) {
            return $this->_header;
        }
        $this->parse();
        return $this->_header;
    }


    /**
     * Author:Robert
     *
     */
    private function parseUrl()
    {
        list($this->_method, $this->_url) = explode(' ', $this->_data, 3);
        $url = parse_url($this->_url);
        $this->_host = $url['host'];
        if (strpos($this->_host, ':') !== false) {
            $this->_isIpv6 = true;
        } else {
            $this->_isIpv6 = false;
        }
        if (strpos($this->_host, ']')) {
            $this->_host = str_replace(['[', ']'], '', $this->_host);
        }
        $this->_port = $url['port'] ?? 80;
    }


    /**
     * Author:Robert
     *
     * @return bool
     */
    public function isIpv6(): bool
    {
        if (is_bool($this->_isIpv6)) {
            return $this->_isIpv6;
        }
        $this->parseUrl();
        return $this->_isIpv6;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function getMethod(): string
    {
        if ($this->_method) {
            return $this->_method;
        }
        $this->parseUrl();
        return $this->_method;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function getUrl(): string
    {
        if ($this->_url) {
            return $this->_url;
        }
        $this->parseUrl();
        return $this->_url;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function getHost(): string
    {
        if ($this->_host) {
            return $this->_host;
        }
        $this->parseUrl();
        return $this->_host;
    }

    /**
     * Author:Robert
     *
     * @return string
     */
    public function getPort(): string
    {
        if ($this->_port) {
            return $this->_port;
        }
        $this->parseUrl();
        return $this->_port;
    }


}
