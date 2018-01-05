<?php

namespace rsk\io;



use Builder;
use rsk\server\server;
use rsk\server\connect;


/**
 * 同步轮询
 * Class ioloop
 * @package rsk\loop
 */
class ioLoop extends loop {




    /**
     * 执行入口
     */
    public function run(){


        // 获取 server对象
        $this->server = Builder::$server;


        //此处设置非阻塞模式,会造成大量CPU资源浪费
        //$this->server->socketSetNonBlock($this->server->getSocket());


        $this->loop(null);

    }



    /**
     * @param $timeout
     */
    public function loop($timeout=null)
    {



        //循环接受 多客户端 连接
        do {


            /**
             * 阻塞模式:
             *      有客户端连接才会返回一个 $connect,否则一直阻塞;
             *      todo 如果客户端是长连接,将无法接收新的客户端连接;
             *      如果客户端是短连接,关闭连接后,才可以接收新的客户端连接;
             *
             * 非阻塞模式:
             *      将会不停轮询,返回一个错误,如果有客户单连接,返回一个 $connect;
             *      todo 如果客户端是长连接;将无法接收新的客户端连接;
             *      如果客户端是短连接,关闭连接后,才可以接收新的客户端连接;
             *
             */
            $connect = $this->server->accept();

            //处理在非阻塞模式下,截断处理,不必去读取客户端消息
            if(!$connect){
                sleep(1);
                console('no client connect','ioloop');
                continue;
            }


            // socket fd
            $fd = $connect->getFd();


            //connect 绑定事件 [接收消息]
            if(!$connect->hasEventHandlers(connect::EVENT_READ)){
                $connect->on(connect::EVENT_READ,[$connect,'receive']);
            }



            //循环接收客户端消息,需要循环读取同一个socket的客户端消息,实现 http 的 keep-alive
            do{


                /**
                 * connect 读取socket消息
                 *
                 *
                 * 在connect中会根据读取的内容以及协议规定,设置connect的连接状态
                 *
                 * 消息、协议 与 连接关闭的关系:
                 * TCP 短连接:读取数据,发送数据,关闭连接;
                 * TCP 长连接:读取数据(阻塞读),发送数据 | 读取数据(阻塞读)[有数据],发送数据 ... | 读取数据(阻塞读)[超时]->关闭连接;
                 * http短连接:读取数据,发送数据,关闭连接;
                 * http长连接:读取数据(阻塞读),发送数据 | 读取数据(阻塞读)[有数据],发送数据 ... | 读取数据(阻塞读)[超时]->关闭连接;
                 *
                 */
                $connReadEvent = $connect->getConnectEvent();
                //阻塞模式读取消息,如果是非阻塞,读取消息为空,就会断开消息
                //$connReadEvent->msg_type = MSG_WAITALL;
                console("=== ".$connReadEvent ." ===","ioloop");
                $connect->trigger(connect::EVENT_READ,$connReadEvent);


                //保持连接状态
                if(connect::STATUS_ACTIVE == $connect->getStatus()){

                    console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_ACTIVE');

                    $receiveEvent       = $this->server->getReceiveEvent();
                    $receiveEvent->fd   = $fd;
                    $receiveEvent->receive_data = $connect->getData();

                    //执行接收消息事件
                    $this->server->trigger(server::EVENT_RSK_RECEIVE,$receiveEvent);

                    //保持连接状态,不需要返回,继续接受消息,但是将引发不会接收新的客户端连接的问题

                }



                //已关闭
                if(connect::STATUS_CLOSE == $connect->getStatus()){

                    console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_CLOSE');

                    //* 接收空消息,客户端主动断开
                    $stopEvent      = $this->server->getStopEvent();
                    $stopEvent->fd  = $fd;

                    //执行停止事件
                    $this->server->trigger(server::EVENT_RSK_STOP,$stopEvent);
                    $this->server->removeConnect($fd);
                    break;
                }



                //短连接状态(返回数据后关闭连接)
                if(connect::STATUS_PEND == $connect->getStatus()){

                    console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_PEND');

                    $receiveEvent       = $this->server->getReceiveEvent();
                    $receiveEvent->fd   = $fd;
                    $receiveEvent->receive_data = $connect->getData();;
                    //执行接收消息事件
                    $this->server->trigger(server::EVENT_RSK_RECEIVE,$receiveEvent);


                    //* 断开连接
                    $stopEvent      = $this->server->getStopEvent();
                    $stopEvent->fd  = $fd;
                    //执行停止事件
                    $this->server->trigger(server::EVENT_RSK_STOP,$stopEvent);
                    $this->server->removeConnect($fd);
                    break;
                }


                sleep(1);

            }while(true);




        } while(true);





    }
}
