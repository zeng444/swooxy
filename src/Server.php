<?php

namespace Swooxy;

use Swooxy\Filter\FilterInterface;
use Swooxy\Protocol\Http;

/**
 * Author:Robert
 *
 * Class Server
 * @package Swooxy
 */
class Server
{

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_options = [
        'daemonize' => false,
        'reactor_num' => 1,
        'worker_num' => 10,
        //            'backlog' => 1000,
        'max_request' => 2000,
        'buffer_output_size' => 12 * 1024 * 1024,
    ];

    /**
     * Author:Robert
     *
     * @var array
     */
    private $_client = [];


    /**
     * Author:Robert
     *
     * @var array
     */
    private $_filters = [];

    /**
     * Author:Robert
     *
     * @var \Swoole\Http\Server
     */
    public $_server;


    /**
     * Server constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if ($options) {
            $this->_options = array_merge($this->_options, $options);
        }
    }

    /**
     * Author:Robert
     *
     * @param $filer
     */
    public function setFilter($filer)
    {
        if (is_array($filer)) {
            $this->_filters = array_merge($this->_filters, $filer);
        } else {
            $this->_filters[] = $filer;
        }
    }

    /**
     * Author:Robert
     *
     * @param string $host
     * @param string $port
     */
    public function createServer(string $host, string $port)
    {
        $this->_server = new \Swoole\Server($host, $port);
    }

    /**
     * Author:Robert
     *
     * @param $tcpMode
     * @return \Swoole\Client
     */
    public function createClient($tcpMode)
    {
        return new \Swoole\Client($tcpMode, SWOOLE_SOCK_ASYNC);
    }

    /**
     * Author:Robert
     *
     * @param $message
     */
    protected function log(string $message)
    {
        echo '['.date('Y-m-d H:i:s').']'.$message.PHP_EOL;
    }

    /**
     * Author:Robert
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->_server->set(array_merge($options, $this->_options));
    }

    /**
     * Author:Robert
     *
     */
    public function onConnect()
    {
        $this->_server->on('connect', function (\Swoole\Server $server, $fd) {
            $this->log("Server connection open: {$fd}");
        });
    }

    /**
     * Author:Robert
     *
     */
    public function onReceive()
    {
        $this->_server->on('receive', function (\Swoole\Server $server, $fd, $reactor_id, $buffer) {
            //判断是否为新连接
            if (!isset($this->_client[$fd])) {

                $http = new Http($buffer);
                //安装filer
                foreach ($this->_filters as $filter) {
                    $filterInstance = new $filter();
                    if (!$filterInstance instanceof FilterInterface) {
                        throw new \Exception('Invalid filter');
                    }
                    if ($filterInstance->run($http) === false) {
                        $server->send($fd, $filterInstance->protocolEndTxt());
                        return $server->close($fd);
                    }
                }

                //识别代理
                $host = $http->getHost();
                $method = $http->getMethod();
                $port = $http->getPort();
                //ipv4/v6处理
                $tcpMode = $http->isIpv6() ? SWOOLE_SOCK_TCP6 : SWOOLE_SOCK_TCP;
                $this->_client[$fd] = $this->createClient($tcpMode);
                if ($method == 'CONNECT') {
                    $this->_client[$fd]->on("connect", function (\Swoole\Client $cli) use ($fd) {
                        $this->log("Tunnel - Connection established");
                        //告诉客户端准备就绪，可以继续发包
                        $this->_server->send($fd, "HTTP/1.1 200 Connection Established\r\n\r\n");
                    });
                } else {
                    $this->_client[$fd]->on("connect", function (\Swoole\Client $cli) use ($buffer) {
                        $this->log("Connection established");
                        //直接转发数据
                        $cli->send($buffer);
                    });
                }
                $this->_client[$fd]->on("receive", function (\Swoole\Client $cli, $buffer) use ($fd) {
                    //将收到的数据转发到客户端
                    if ($this->_server->exist($fd)) {
                        $this->_server->send($fd, $buffer);
                    }
                });
                $this->_client[$fd]->on("error", function (\Swoole\Client $cli) use ($fd) {
                    $this->log("Client {$fd} error");
                });

                $this->_client[$fd]->on("close", function (\Swoole\Client $cli) use ($fd) {
                    $this->log("Client {$fd} connection close");
                });

                $this->_client[$fd]->connect($host, $port);
            } else {
                //已连接，正常转发数据
                if ($this->_client[$fd]->isConnected()) {
                    $this->_client[$fd]->send($buffer);
                }
            }
        });
    }

    public function onClose()
    {
        $this->_server->on('close', function (\Swoole\Server $server, $fd) {
            $this->log("Server connection close: {$fd}");
            unset($this->_client[$fd]);
        });
    }

    /**
     * Author:Robert
     *
     * @return mixed
     */
    public function start()
    {
        return $this->_server->start();
    }

    /**
     * Author:Robert
     *
     * @param string $host
     * @param int $port
     * @return mixed
     */
    public function listen($host = '0.0.0.0', $port = 10080)
    {
        $this->createServer($host, $port);
        $this->setOptions();
        $this->onConnect();
        $this->onReceive();
        $this->onClose();
        return $this->start();
    }
}