# Swooxy

> An HTTP proxy server based on swoole


## Feature

- [x] Http proxy
- [x] Https proxy
- [x] Request filters

## Installation

```
composer require janfish/swooxy -vvv
```

## How to use

- Standard mode

```php
$proxy = new \Swooxy\Server();
$proxy->listen();
```

- Define host and port

```php
$proxy = new \Swooxy\Server([
    'daemonize' => false,
]);
$proxy->listen('0.0.0.0', 10086);
```

- Filter mode

```php
$proxy = new \Swooxy\Server([
    'daemonize' => false,
]);
$proxy->setFilter([
    Rule::class, 
    Log::class, 
]);
$proxy->listen('0.0.0.0', 10086);
```

## How to add a request filter

```php
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

```