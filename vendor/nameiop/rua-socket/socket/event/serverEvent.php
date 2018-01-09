<?php


namespace rsk\event;


use rua\base\event;

class serverEvent extends event{


    /**
     * server
     * @var \rsk\server\server
     */
    public $server;


    /**
     *
     * @var int
     */
    public $fd=0;


}