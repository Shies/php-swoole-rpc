<?php
/**
 * 发送请求客户端层.
 * User: gukai@bilibili.com
 * Date: 16/8/15
 * Time: 下午6:14
 */
namespace RPC;

class Client
{
    protected $client;
    protected $remote;
    protected $function = ['Handle', 'shakehands'];


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
        $class = current($this->function);
        if ($this->remote !== null) {
            $class = $this->remote;
        }

        $this->send(['class' => $class, 'method' => $method, 'args' => $args]);
    }


    public function connect($host = '0.0.0.0', $port = 9501)
    {
        if (!$fp = $this->client->connect($host, $port, 1)) {
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

            if (!$msg) {
                list($class, $method) = $this->function;
            } else {
                $class = current($this->function);
                $method = $msg;
            }

            $this->send(['class' => $class, 'method' => $method]);
        });
    }


    public function onReceive($cli, $data)
    {
        if (!empty($data)) {
            echo "Received: ".$data."\n";
        }

        return $this->client->close();
    }


    public function onClose($cli)
    {
        echo "Client close connection\n";
    }


    public function onError()
    {
        if (!$this->isConnected()) {
            echo "Error: no connect\n";
            return;
        }
    }


    public function send($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        return $this->client->send($data);
    }


    public function isConnected()
    {
        return $this->client->isConnected();
    }

}