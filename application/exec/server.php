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


    //广播上线通知
    $onlineConn = $server->getConnect();




    //获取所有在线用户
    $on_fd = array();
    foreach($onlineConn as $conn){
        $connFd = $conn->getFd();
        if( $connFd != $fd ) {
            $on_fd[] = $connFd;
        }
    }


    //广播
    foreach($onlineConn as $conn){
        $connFd = $conn->getFd();
        if( $connFd == $fd ){
            $server->send($fd,'您好,您已连接,编号:['.$fd.'],在线用户['.implode('|',$on_fd).']');
        }else{
            $server->send($connFd,'新用户上线,编号:['.$fd.']');
        }
    }



});





/**
 * 接收客户端消息
 *
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_RECEIVE,function(\rsk\event\receiveEvent $event){


    $server = $event->server;
    $fd     = $event->fd;
    $data   = $event->receive_data;
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

    //console('fd['.$fd.'] data : ['.$data.']','send-bin');




    if(strpos($data,'|')){
        //向指定客户端发送消息
        list($send_fd,$data) = explode('|',$data);


        if($server->getConnect($send_fd)){

            //组合消息
            $send_data = $data.'|'.$fd.PHP_EOL;
            $server->send($send_fd,$send_data);
            console("接收到[".$fd."]消息:".$data.',并向['.$send_fd.']返回','server-exec');
            return;
        }
    }

    //发送系统消息
    $send_data = '您没有指定客户端,消息: ['.$data.'] 发送失败...'.PHP_EOL;
    $server->send($fd,$send_data.PHP_EOL);
    console("没有指定客户端发送,直接返回系统消息到:[".$fd."]",'server-exec');

});




/**
 * 客户端断开
 *
 */
Builder::$server->on(\rsk\server\server::EVENT_RSK_STOP,function(\rsk\event\stopEvent $event){



    $server = $event->server;
    $fd = $event->fd;


    //广播下线通知
    $onlineConn = $server->getConnect();
    foreach($onlineConn as $conn){
        $connFd = $conn->getFd();
        if( $connFd == $fd ){
            //$server->send($fd,'您好,您已连接,编号:['.$fd.']');
            $server->close($fd);
        }else{
            $server->send($connFd,'用户['.$fd.']下线');
        }
    }
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