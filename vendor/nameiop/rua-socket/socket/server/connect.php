<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/28
 * Time: 11:44
 */
namespace rsk\server;


use Builder;
use app\protocol\http;
use rua\traits\eventable;
use rsk\traits\socketable;
use rsk\traits\streamsocketable;
use rsk\event\connect\readEvent;


class connect {


	//use streamsocketable,eventable;
    use socketable,eventable;


    //活动状态:长连接
    const STATUS_ACTIVE = 1;

    //等待状态(准备关闭):短连接
    const STATUS_PEND = 2;

    //关闭状态
    const STATUS_CLOSE = 3;




    /**
     * 读取客户端消息事件
     */
    const EVENT_READ = 'read';



    //当前连接状态
    private $status;


    /**
     * @var \rsk\protocol\protocol
     */
    protected $protocol;


    /**
     * 接收客户端消息 触发事件参数
     * @var object|null
     */
    private $_readEvent;


    /**
     * 初始化
     * @param $socket
     */
    public function __construct($socket){
        $this->socket = $socket;
        $this->fd = socket_to_fd($socket);
        $this->status = $this->fd ? self::STATUS_ACTIVE : self::STATUS_CLOSE;
    }




	/*
     * 获取连接状态
     * @return integer
     * @author liu.bin 2017/9/28 14:21
     */
    public function getStatus(){
        return $this->status;
    }





    /**
     * 设置连接状态
     * @param $status integer
     * @author liu.bin 2017/9/28 14:57
     */
    public function setStatus($status){
        $this->status = $status;
    }









    /**
     * 读取客户端消息
     *
     * 此处由 io模型 和 self递归 调用
     *
     * @param \rsk\event\connect\readEvent $readEvent
     * @return bool
     *
     * @author liu.bin 2017/9/29 13:24
     */
    public function receive($readEvent){


        //连接关闭,不再接收客户端消息
        if(self::STATUS_ACTIVE !== $this->status){
            return false;
        }


        //获取协议对象
        $protocol = Builder::$app->get('protocol');



        /**
         * $bufferSize:需要从socket读取的数据长度
         *
         * 固定包头+包体 协议:
         *      buffer_size 在协议中会变化,主要因为解决粘包
         * 边界eof检测 协议:
         *      buffer_size 固定,不会变化
         */
        $bufferSize = $protocol->getBufferSize();



        //根据触发事件,处理读取客户端socket消息的模式
        if( readEvent::SOCKET_READ == $readEvent->read_type){
            $buffer = $this->socketRead($this->socket,$bufferSize,$readEvent->read_param);
        }else{
            //默认模式
            $buffer = $this->socketReceive($this->socket,$bufferSize,$readEvent->read_param);
        }



        /**
         * 协议判断是否需要继续从客户端读取消息(协议需要检测消息的完整性)
         *
         * true:    读取结束,不需要继续读取;
         * false:   继续读取客户端消息;
         * null:    读取错误,超过最大长度,客户端异常断开
         *
         * $readEvent:  可根据协议,动态修改$readEvent的读取数据方式,读取参数
         */
        $eof = $protocol->readEOF($buffer);



        // 1) 消息不完整,继续读取
        if(false === $eof){
            $this->trigger(self::EVENT_READ,$readEvent);
        }



        // 2) 读取错误
        if(null === $eof){
            $this->status = self::STATUS_CLOSE;
            return false;
        }



        //3) 消息读取完整
        if(true === $eof){

            //根据具体协议判断客户端连接是否需要断开;
            switch($protocol->getConnectLife()){

                case http::CONNECT_KEEP:
                    $this->status = self::STATUS_ACTIVE;
                    break;
                case http::CONNECT_CLOSE:
                    $this->status = self::STATUS_CLOSE;
                    break;
                case http::CONNECT_ONCE:
                    $this->status = self::STATUS_PEND;
                    break;
                default:
                    $this->status = self::STATUS_CLOSE;
                    break;
            }

            return true;

        }


        return true;
    }





    /**
     * 接收客户端消息事件参数
     * @return \rsk\event\connect\readEvent;
     */
    public function getReadEvent(){

        if(is_null($this->_readEvent)){
            $this->_readEvent = new readEvent();
            $this->_readEvent->connect = $this;
        }
        $this->_readEvent->fd = $this->getFd();
        return $this->_readEvent;

    }




    /**
     * 获取消息
     * @return string
     */
    public function getData(){
        return Builder::$app->get('protocol')->getData();
    }


}

