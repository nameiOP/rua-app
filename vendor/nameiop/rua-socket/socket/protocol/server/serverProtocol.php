<?php
namespace rsk\protocol\server;


use rsk\protocol\protocol;



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
     * 重置数据
     * @author liu.bin 2017/9/30 10:51
     */
    public function over()
    {
        $this->buffer = '';
        $this->readBuffer = '';
        $this->readLength = 0;
    }


}