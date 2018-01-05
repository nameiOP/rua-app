<?php

namespace rsk\io;



use Builder;
use pfork\phpfork;
use rsk\server\server;
use rsk\server\connect;


/**
 * 多进程
 * Class ioloop
 * @package rsk\loop
 */
class ioFork extends loop {


    /**
     * @var \pfork\phpfork;
     */
    private $_fork;


    /**
     * 执行入口
     */
    public function run(){


        // 获取 server对象
        $this->server = Builder::$server;


        //此处设置非阻塞模式,会造成大量CPU资源浪费
        //$this->server->socketSetNonBlock($this->server->getSocket());


        $this->_fork = new fork();
        $this->_fork->bindServer($this->server);


        $this->loop(null);

    }



    /**
     * @param $timeout
     */
    public function loop($timeout=null)
    {





        do {

            //接受客户端连接 同步会阻塞
            $connect = $this->server->accept();
            if(!$connect){
                continue;
            }


            $this->_fork->bindConnect($connect);
            $this->_fork->start();


        } while(true);



    }
}





/**
 * Class fork
 * @package rsk\io
 */
class fork extends phpfork
{


    /**
     * 多进程入口
     */
    protected function run(){


        $server = $this->getServer();
        $connect = $this->getConnect();
        $fd = $connect->getFd();


        //connect 绑定事件 [接收消息]  如果已绑定,则不需要再绑定
        if(!$connect->hasEventHandlers(connect::EVENT_RECEIVE)){
            $connect->on(connect::EVENT_RECEIVE,[$connect,'receive']);
        }



        //keep-alive 需要循环读取同一个socket的客户端消息,实现 http 的 keep-alive
        do {

            console('父进程:'.$this->getParentPid().',  子进程:'.$this->getPid(),'子进程');

            //connect 执行事件[接收消息]
            $connect->trigger(connect::EVENT_RECEIVE);
            $receive_data = $connect->getData();


            //* 接收到消息
            if(is_empty($receive_data) || connect::STATUS_CLOSE == $connect->getStatus()){

                //客户端关闭连接
                $stopEvent      = $server->getStopEvent();
                $stopEvent->fd  = $fd;
                $server->trigger(server::EVENT_RSK_STOP,$stopEvent);
                $server->removeConnect($fd);
                break;


            }else{


                $receiveEvent       = $server->getReceiveEvent();
                $receiveEvent->fd   = $fd;
                $receiveEvent->receive_data = $receive_data;

                //执行接收消息事件
                $server->trigger(server::EVENT_RSK_RECEIVE,$receiveEvent);
            }



        }while(true);



    }


}










