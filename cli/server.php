<?php


/**
 * 定义常量
 */
defined('RUA_DEBUG')    or define('RUA_DEBUG',true);
defined('RUA_ENV')      or define('RUA_ENV','dev');
defined('RUA_ENV_DEV')  or define('RUA_ENV_DEV', true);



//引入composer
require(__DIR__ . '/../vendor/autoload.php');


//引入框架文件
//require(__DIR__ . '/../vendor/nameiop/rua/framework/Builder.php');
//require(__DIR__ . '/../vendor/nameiop/rua/framework/event/event.php');



//配置文件
$config = require(__DIR__ . '/../config/cli.php');


//初始化app
$app = new rua\app\app($config);


//绑定server
Builder::$server = Builder::$app->get('server');



/**
 * socket 启动
 */
Builder::$server->on(\rsk\server\server::START,function($server){


    $server = $event->server;




});




/**
 * socket 连接
 *
 *
 */
Builder::$server->on(\rsk\server\server::ACCEPT,function(\rsk\server\event\acceptEvent $event){

    $server = $event->server;
    $fd     = $event->fd;


    //获取所有在线用户
    $ioBuffers = \rsk\collect\bufferCollect::getItem();
    $onLineFds = array_keys($ioBuffers->toArray());

//    return ;
//    //广播
//    foreach($ioBuffers as $buffer){
//        $_fd = $buffer->getConnect()->socketAble()->getFd();
//        if( $_fd == $fd ){
//            $server->send($fd,'您好,您已连接,编号:['.$fd.'],在线用户['.implode('|',$onLineFds).']'.PHP_EOL);
//        }else{
//            $server->send($_fd,'新用户上线,编号:['.$fd.']'.PHP_EOL);
//        }
//    }



});





/**
 * 接收客户端消息
 *
 *
 */
Builder::$server->on(\rsk\server\server::MESSAGE,function(\rsk\server\event\messageEvent $event){


    $server = $event->server;
    $fd     = $event->fd;
    $data   = $event->message;

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

    console('fd['.$fd.'] data : ['.$data.']','send-bin');


    $server->send($fd,strtoupper($data));

});




/**
 * 客户端断开
 *
 */
Builder::$server->on(\rsk\server\server::STOP,function(\rsk\server\event\stopEvent $event){

    $server = $event->server;
    $fd = $event->fd;

    console("\r\n=====================".$fd." off line ===============================",'server-bin');
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