<?php


/**
 * 初始化服务器信息
 *
 *
 */
Builder::$server = Builder::$app->get('server');



/**
 * socket 启动
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_START,function( \rsk\event\startEvent $event){


    $server = $event->server;




});




/**
 * socket 连接
 *
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_ACCEPT,function(\rsk\event\acceptEvent $event){

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
    $data = $event->receive_data;

    /*
    $mess = '<h3>now time is : '.date('Y-m-d H:i:s',time()).'<h3>';
    $mess .= "<h3>online client num:".$server->getConnect()->count()."</h3>\r\n";
    $length = strlen($mess);

    $response = "HTTP/1.1 200 OK\r\n";
    $response .= "Date: Mon, 10 Aug 2015 06:22:08 GMT\r\n";
    $response .= "Connection: Keep-Alive\r\n";
    $response .= "Content-Length: ".$length."\r\n";
    $response .= "Content-Type: text/html;charset=utf-8\r\n";
    $response .= "\r\n";
    $response .= $mess;
    $response .= "\r\n";

    console('[[ send ]] to client fd :'.$fd .' - online client num :'.$server->getConnect()->count(),'server-bin');
    $server->send($fd,$response);
    */


    $server->send($fd,$data);

});




/**
 * 客户端断开
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_STOP,function(\rsk\event\stopEvent $event){

    $server = $event->server;
    $fd = $event->fd;
    console('[[ stop ]]client stop connect [fd] '.$fd, 'server-bin');
    console("\r\n=====================".$fd."====================================",'server-bin');
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