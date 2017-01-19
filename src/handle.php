<?php
/**
 * Created by PhpStorm.
 * User: gukai@bilibili.com
 * Date: 16/8/18
 * Time: 下午3:38
 */
namespace RPC;

trait Handle
{

    protected $text = 'let\'s say';


    public function hello()
    {
        $helo = $this->text.' hello';

        return $helo;
    }


    public function world()
    {
        $world = $this->hello();
        $world .= ' world';

        return $world;
    }


    public function shakehands()
    {
        $methods = get_class_methods($this);

        return $methods;
    }

}