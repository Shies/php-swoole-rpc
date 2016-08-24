<?php
/**
 * Created by PhpStorm.
 * User: gukai@bilibili.com
 * Date: 16/8/15
 * Time: 下午5:40
 */
namespace rpc;
use rpc\handle;

class rpcServer
{
    use handle;
    private $serv;
    private static $config = [
        'worker_num'  => 8,
        'daemonize'   => false, // 是否作为守护进程,此配置一般配合log_file使用
        'max_request' => 1000,
        'log_file'    => './swoole.log',
        'task_worker_num' => 8
    ];


    public function __construct()
    {
        $this->serv = new \swoole_server('0.0.0.0', 9501);
        // 初始化swoole服务
        $this->serv->set(static::$config);
        // 设置监听
        $this->serv->on('Start',   [$this, 'onStart']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on("Receive", [$this, 'onReceive']);
        $this->serv->on("Close",   [$this, 'onClose']);
        $this->serv->on("Task",    [$this, 'onTask']);
        $this->serv->on("Finish",  [$this, 'onFinish']);
        //开启
        $this->serv->start();
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
        static $error;
        try
        {
            $param = ['fd' => $fd];
            $param = array_merge(json_decode($data, true), $param);
            $serv->task(json_encode($param));
        }
        catch (\Exception $e)
        {
            $error = $e->getMessage();
        }

        return $error;
    }


    public function onClose($serv, $fd)
    {
        echo "Client Close.\n";
    }


    public function onTask($serv, $task_id, $from_id, $data)
    {
        $fd = json_decode($data, true);

        $result = 'no data';
        if (method_exists($this, $fd['method']))
        {
            $result = call_user_func([&$this, $fd['method']]);
        }

        if (is_array($result))
        {
            $result = json_encode($result);
        }

        $serv->send($fd['fd'], 'Swoole: '.$result);
    }


    public function onFinish($serv, $task_id, $data)
    {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
    }

}
