<?php




/**
 * 初始化服务器信息
 *
 *
 */
Builder::$client = Builder::$app->get('client');



/**
 * 客户端socket 启动
 */
Builder::$client->on(\rsk\client\client::EVENT_RSK_START,function( \rsk\event\client\startEvent $event){


    show_system('客户端启动成功');

});




/**
 * 客户端socket 连接成功
 *
 *
 */
Builder::$client->on(\rsk\client\client::EVENT_RSK_CONNECT,function(\rsk\event\client\connectEvent $event){



    $client = $event->client;


    //新起一个进程 接收服务器的消息
    $fork = new \app\pforks\fork();
    $fork->client = $client;
    $fork->start();


});





/**
 * 接收服务端消息
 */
Builder::$client->on(\rsk\client\client::EVENT_RSK_RECEIVE,function(\rsk\event\client\receiveEvent $event){

    $data = $event->receive_data;


    if(strpos($data,'|')){
        list($data,$from_fd) = explode('|',$data);
        $from_fd = trim($from_fd,PHP_EOL);
        show_from($data,$from_fd);
    }else{
        show_system($data);
    }



});




/**
 * 客户端断开
 *
 */
Builder::$client->on(\rsk\client\client::EVENT_RSK_STOP,function(\rsk\event\client\stopEvent $event){


});






/**
 * 重启
 *
 */
Builder::$client->on('RESTART',function($event){

    console('restart');
});





// 启动 client
Builder::$client->start();

//接收用户输入
while($input = fgets(STDIN)){
    Builder::$client->send($input.PHP_EOL);
    show_send($input);
}


















