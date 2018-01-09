<?php


namespace rsk\event\client;


use rua\base\event;


/**
 * 客户端事件父类
 * Class clientEvent
 * @package rsk\event\client
 */
class clientEvent extends event{


    /**
     * @var \rsk\client\client
     */
    public $client;


}