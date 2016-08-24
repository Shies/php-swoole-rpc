<?php
/**
 * Created by PhpStorm.
 * User: bilibili
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
            $server = (new rpc\rpcServer());
        break;
        case "client":
            $client = (new rpc\rpcClient());
            $client->connect();
            $client->shakehands();
        break;
    }
}


