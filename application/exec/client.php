<?php


/**
 * 初始化服务器信息
 *
 *
 */
Builder::$client = Builder::$app->get('client');



/**
 * socket 启动
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_START,function( \rsk\event\startEvent $event){


    $server = $event->server;

    console('start');


});




/**
 * socket 连接
 *
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_CONNECT,function(\rsk\event\connectEvent $event){

    $server = $event->server;
    $fd = $event->fd;
    //console($fd . ' 连接','客户端');
    //$server->send($fd,'欢迎连接');
});





/**
 * 接收客户端消息
 *
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_RECEIVE,function(\rsk\event\receiveEvent $event){


    $server = $event->server;
    $fd = $event->fd;
    //$data = $event->receive_data;

    $mess = 'now time is : '.date('Y-m-d H:i:s',time());
    $length = strlen($mess);

    $response = "HTTP/1.1 200 OK\r\n";
    $response .= "Date: Mon, 10 Aug 2015 06:22:08 GMT\r\n";
    $response .= "Connection: keep-alive\r\n";
    $response .= "Content-Length: ".$length."\r\n";
    $response .= "Content-Type: text/html;charset=utf-8\r\n\r\n";
    $response .= $mess;
    $response .= "\r\n";

    console('send to client fd :'.$fd .' - online num :'.count($server->getConnSocket()),'send');
    $server->send($fd,$response);
});




/**
 * 客户端断开
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_STOP,function(\rsk\event\stopEvent $event){

    $server = $event->server;
    $fd = $event->fd;
    console('client stop connect [fd] '.$fd, 'server');
    console("\r\n==============================\r\n");
    $server->close($fd);
});






/**
 * 重启
 *
 */
Builder::$server->on('RESTART',function($event){

    console('restart');
});






// 启动 server
Builder::$server->start();