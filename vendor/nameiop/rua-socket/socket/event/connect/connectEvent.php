<?php


namespace rsk\event\connect;


use rua\base\event;

class connectEvent extends event{


    /**
     * server
     * @var \rsk\server\connect
     */
    public $connect;


    /**
     *
     * @var int
     */
    public $fd=0;



}