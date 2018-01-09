<?php

namespace app\pforks;


use pfork\phpfork;
use rsk\client\client;
use rsk\event\client\receiveEvent;


/**
 * 开启一个进程
 * Class fork
 * @package rsk\io
 */
class fork extends phpfork
{


    /**
     * @var client;
     */
    public $client;


    /**
     * 子进程入口
     */
    protected function run(){


        while($data = $this->client->receive()){

            $event = new receiveEvent();
            $event->client = $this->client;
            $event->receive_data = $data;
            $this->client->trigger(client::EVENT_RSK_RECEIVE,$event);
        }


    }


}
