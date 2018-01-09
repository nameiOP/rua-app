<?php

namespace rsk\io;



use Builder;
use rsk\event\connect\readEvent;
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


        // 轮询IO模式,必须要阻塞模式,否则无法读取消息,会自动当作关闭连接
        $this->server->socketSetBlock($this->server->getSocket());

        //$this->server->socketSetNonBlock($this->server->getSocket());

        $this->loop($this->time_out);

    }



    /**
     * @param $timeout
     */
    public function loop($timeout=null)
    {



        //[循环] 保证多客户端连接
        do {


            /**
             * 阻塞模式:
             *      有客户端连接才会返回一个 $connect,否则一直阻塞;
             *      stream系列函数无法做到,socket可以做到
             *      如果客户端是长连接,将无法接收新的客户端连接;
             *      如果客户端是短连接,关闭连接后,才可以接收新的客户端连接;
             *
             * 非阻塞模式:(不适用)
             *      会持续性读取不到消息,但是协议中,读取不到消息会关闭客户端连接,所以不适用此 IO-loop 模型
             *
             */
            $connect = $this->server->accept();
            if(!$connect){
                console('no client connect','ioloop');
                continue;
            }




            // socket fd
            $fd = $connect->getFd();


            $address = $connect->_from_address;
            $port = $connect->_from_port;
            //console('accept success,id ['.$fd.'] ,address ['.$address.'] ,port ['.$port.']','ioloop');


            //connect 绑定事件 [接收消息]
            if(!$connect->hasEventHandlers(connect::EVENT_READ)){
                $connect->on(connect::EVENT_READ,[$connect,'receive']);
            }



            //[循环] 保证单客户端的多条消息读取
            do{

                //console('loop begin,id ['.$fd.']','ioloop');


                $connReadEvent = Builder::$app->get('protocol')->getReadEvent();

                //必须阻塞读取,如果不阻塞读取,则会自动断开
                $connect->trigger(connect::EVENT_READ,$connReadEvent);


                //保持连接状态
                if(connect::STATUS_ACTIVE == $connect->getStatus()){

                    //console('STATUS_ACTIVE - fd['.$fd.'] | buffer : '.$connect->getReadData(),'ioloop');


                    $receiveEvent       = $this->server->getReceiveEvent();
                    $receiveEvent->fd   = $fd;
                    $receiveEvent->receive_data = $connect->getReadData();

                    //执行接收消息事件
                    $this->server->trigger(server::EVENT_RSK_RECEIVE,$receiveEvent);

                    //保持连接状态,不需要返回,继续接受消息,但是将引发不会接收新的客户端连接的问题

                }



                //已关闭
                if(connect::STATUS_CLOSE == $connect->getStatus()){

                    //console('connect fd ['.$fd.'] protocol fd ['.Builder::$app->get('protocol'),'select - STATUS_CLOSE');

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

                    //console('connect fd '.$fd.' status:'.$connect->getStatus(),'select - STATUS_PEND');

                    $receiveEvent       = $this->server->getReceiveEvent();
                    $receiveEvent->fd   = $fd;
                    $receiveEvent->receive_data = $connect->getReadData();;
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

            }while(true);




        } while(true);





    }
}
