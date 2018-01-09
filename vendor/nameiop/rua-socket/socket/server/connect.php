<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/28
 * Time: 11:44
 */
namespace rsk\server;


use Builder;
use rua\traits\eventable;
use rsk\traits\socketable;
use rsk\traits\streamsocketable;
use rsk\event\connect\readEvent;
use rsk\protocol\server\serverProtocol;

class connect {


	use streamsocketable,eventable;
    //use socketable,eventable;


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
    public $status;




    /**
     * @var string 客户端地址
     */
    public $_from_address = '';


    /**
     * @var int 客户端端口
     */
    public $_from_port = 0;


    /**
     * @var int 连接服务器时间⌚️
     */
    public $_accept_time = 0;



    //协议
    private $_protocol = null;


    /**
     * 初始化
     */
    public function __construct(){
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
     * 获取协议对象
     */
    public function getProtocol(){
        if(is_null($this->_protocol)){
            $this->_protocol = clone Builder::$app->get('protocol');
        }
        return $this->_protocol;
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
        $protocol = $this->getProtocol();



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

                case serverProtocol::CONNECT_KEEP:
                    $this->status = self::STATUS_ACTIVE;
                    break;
                case serverProtocol::CONNECT_CLOSE:
                    $this->status = self::STATUS_CLOSE;
                    break;
                case serverProtocol::CONNECT_ONCE:
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
     * 获取消息
     * @return string
     */
    public function getReadData(){
        return $this->getProtocol()->getReadData();
    }


}

