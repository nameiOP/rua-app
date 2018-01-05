<?php


require(__DIR__ . '/BaseBuilder.php');




class Builder extends BaseBuilder{


    /**
     *
     * @var rsk\server\server
     */
    public static $server;


    /**
     *
     * @var
     */
    public static $client;


}

spl_autoload_register(['Builder','autoload'],true,true);
Builder::$container = new rua\base\di\container();