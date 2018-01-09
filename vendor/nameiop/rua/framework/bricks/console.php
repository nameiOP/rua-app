<?php
namespace rua\bricks;
use rua\able\sendable;


/**
 * 控制台调试
 * Class console
 * @package rua\console
 */
class console implements sendable {


    /**
     * 发送消息
     * @param string $mess
     * @author liu.bin 2017/10/25 14:42
     */
    public function send($mess)
    {
        echo $mess.PHP_EOL;
    }

}