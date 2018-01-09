<?php
namespace rsk\protocol\server;


use rsk\protocol\protocol;
use rsk\event\connect\readEvent;


abstract class serverProtocol extends protocol
{


    /**
     * 只连接一次,然后关闭
     */
    const CONNECT_ONCE = 1;


    /**
     * 保持连接
     */
    const CONNECT_KEEP = 2;


    /**
     * 立即关闭
     */
    const CONNECT_CLOSE = 3;



    /**
     * @var int 连接生命周期
     */
    private $_connect_life;



    /**
     * 接收客户端消息 触发事件参数
     * @var object|null
     */
    private $_readEvent;


    /**
     * 构造器
     */
    public function __construct(){
        $this->_connect_life = self::CONNECT_KEEP;
    }



    /**
     * 获取连接状态
     */
    public function getConnectLife(){
        return $this->_connect_life;
    }


    /**
     * 设置连接状态
     * @param $val
     */
    public function setConnectLife($val){
        $this->_connect_life = $val;
    }







    /**
     * 接收客户端消息事件参数
     * @return \rsk\event\connect\readEvent;
     */
    public function getReadEvent(){

        if(is_null($this->_readEvent)){

            //创建对象,可以根据协议的类型,设置消息读取的方式
            $eventConfig = [
                'class'         => '\rsk\event\connect\readEvent',
                'read_type'     => readEvent::SOCKET_READ,
                'read_param'    => readEvent::SOCKET_READ_PARAM_BINARY,//按指定长度读
            ];
            $this->_readEvent = \Builder::createObject($eventConfig);
        }

        return $this->_readEvent;

    }




    /**
     * 重置数据
     * @author liu.bin 2017/9/30 10:51
     */
    public function bufferRecovery()
    {
        $this->buffer = '';
        $this->readBuffer = '';
        $this->readLength = 0;
    }


}