<?php
/**
 * 启动server或者client.
 * User: gukai@bilibili.com
 * Date: 16/8/18
 * Time: 下午12:01
 */
require __DIR__ . '/vendor/autoload.php';

$argv = !empty($GLOBALS['argv']) ? $GLOBALS['argv'] : [];
foreach ($argv AS $val)
{
    switch ($val)
    {
        case "server":
            $server = (new RPC\Server());
            $server->start();
        break;
        case "client":
            $client = (new RPC\Client());
            $client->connect();
            $client->shakehands();
        break;
    }
}


