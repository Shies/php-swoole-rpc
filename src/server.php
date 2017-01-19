<?php
/**
 * Created by PhpStorm.
 * User: gukai@bilibili.com
 * Date: 16/8/15
 * Time: 下午5:40
 */
namespace RPC;
use RPC\Handle;

class Server
{
    use Handle;
    protected $serv;
    const NO_DATA = 'no response';


    public function __construct()
    {
        $this->serv = new \swoole_server('0.0.0.0', 9501);
        // 设置监听
        $this->serv->on('Start',   [$this, 'onStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on("Receive", [$this, 'onReceive']);
        $this->serv->on("Close",   [$this, 'onClose']);
        $this->serv->on("Task",    [$this, 'onTask']);
        $this->serv->on("Finish",  [$this, 'onFinish']);
        // 初始化swoole服务
        $this->serv->set([
            'worker_num'  => 8,
            'daemonize'   => false, // 是否作为守护进程,此配置一般配合log_file使用
            'max_request' => 1000,
            'log_file'    => './swoole.log',
            'task_worker_num' => 8
        ]);
    }


    public function start()
    {
        return $this->serv->start();
    }


    public function onStart($serv)
    {
        echo SWOOLE_VERSION . " onStart\n";
    }


    public function onConnect($serv, $fd)
    {
        echo $fd."Client Connect.\n";
    }


    public function onReceive($serv, $fd, $from_id, $data)
    {
        static $accept;
        try {
            $param = ['fd' => $fd];
            $param = array_merge(json_decode($data, 1), $param);
            $serv->task(json_encode($param));
        } catch (\Exception $e) {
            throw $e;
        }

        return ($accept = true);
    }


    public function onClose($serv, $fd)
    {
        echo "Client Close.\n";
    }


    public function onTask($serv, $task_id, $from_id, $data)
    {
        $fd = json_decode($data, 1);

        $resp = null;
        if (method_exists($this, $fd['method'])) {
            $resp = call_user_func([&$this, $fd['method']]);
        }

        if (is_null($resp)) {
            $resp = static::NO_DATA;
        }

        if (!is_string($resp)) {
            $resp = json_encode($resp);
        }

        $fd['resp'] = (string) $resp;
        $serv->finish(json_encode($fd));
    }



    public function onFinish($serv, $task_id, $data)
    {
        $fd = json_decode($data, 1);
        if (!isset($fd['fd'])) {
            return false;
        }

        $serv->send($fd['fd'], "Result: {$fd['resp']}\n");
    }

}
