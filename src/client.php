<?php
/**
 * Created by PhpStorm.
 * User: gukai@bilibili.com
 * Date: 16/8/15
 * Time: 下午6:14
 */
namespace rpc;

class rpcClient
{
    private $client;
    private $port = '9501';
    private $host = '127.0.0.1';
    private $rpc_class = 'rpcServer';
    private $rpc_method = 'sharkhands';


    public function __construct()
    {
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->on('Connect', [$this, 'onConnect']);
        $this->client->on('Receive', [$this, 'onReceive']);
        $this->client->on('Close',   [$this, 'onClose']);
        $this->client->on('Error',   [$this, 'onError']);
    }


    public function __call($method, $args)
    {
        $params = ['method' => $method, 'args' => $args];
        $this->send(json_encode($params));
    }


    public function connect()
    {
        if (!$fp = $this->client->connect($this->host, $this->port, 1))
        {
            echo "Error: {$fp->errMsg}[{$fp->errCode}]\n";
            return;
        }
    }


    // connect之后, 会调用onConnect方法
    public function onConnect($cli)
    {
        fwrite(STDOUT, "Enter Msg:");
        swoole_event_add(STDIN, function() {
            fwrite(STDOUT, "Enter Msg:");
            $msg = trim(fgets(STDIN));

            $class = 'handle';
            $method = $msg;

            $param = ['class' => $class, 'method' => $method];
            $this->send(json_encode($param));
        });
    }


    public function onReceive($cli, $data)
    {
        if (!empty($data))
        {
            echo "Received: ".$data."\n";
        }
        $this->client->close();
    }


    public function onClose($cli)
    {
        echo "Client close connection\n";
    }


    public function onError()
    {
        if (!$this->isConnected())
        {
            echo "Error: no connect\n";
            return;
        }
    }


    public function send($data)
    {
        $this->client->send($data);
    }


    public function isConnected()
    {
        return $this->client->isConnected();
    }

}