<?php
include_once dirname(__DIR__).'/vendor/autoload.php';
include_once './Filter/rule.php';
include_once './Filter/Log.php';
$proxy = new \Swooxy\Server([
    'daemonize' => false,
]);
$proxy->setFilter([
    Rule::class, //记录规则
    Log::class, //写日志
]);
$proxy->listen('0.0.0.0', 10080);