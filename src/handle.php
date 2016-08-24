<?php
/**
 * Created by PhpStorm.
 * User: bilibili
 * Date: 16/8/18
 * Time: 下午3:38
 */
namespace rpc;

trait handle
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