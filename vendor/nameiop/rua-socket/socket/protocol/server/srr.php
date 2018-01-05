<?php
namespace rsk\protocol\server;


/**
 * Class roo
 * @package rsk\protocol\server
 *
 * 请求响应协议:
 * socket有消息后,connect读取固定长度(bufferSize)的数据包。并当作一个完整数据包后直接响应
 * 适用范围 : 长连接,短连接,开发调试,心跳检测
 */
class srr extends serverProtocol
{

	
	/**
     * 数据解包
     * @param $buffer string
     * @return string
     * */
    public function decode($buffer){
        $buffer = str_replace(PHP_EOL, '', $buffer);
        return $buffer;
	}

    /**
     * 数据打包
     * @param $buffer string
     * @return string
     * */
    public function encode($buffer){
        $buffer = str_replace(PHP_EOL, '', $buffer);
		return $buffer;
	}




    /**
     * 是否通知connect继续读取消息
     *
     * readBuffer只会返回false
     *
     * @param string $buffer
     * @return bool false:不需要继续接收消息 ，true:继续接收消息
     * @author liu.bin 2017/9/29 14:37
     */
    public function readBuffer($buffer = '')
    {

        //消息格式不正确
        if('' === $buffer || is_null($buffer)){
            $this->over();
            return false;
        }


        /**
         * 已接收的字节 >= 最大长度的话:
         * 就不用接收消息,防止内存泄漏,需要数据重置
         */
        if($this->readLength >= $this->maxReadLength){
            $this->over();
            return false;
        }


        /**
         * buffer解码
         */
        $this->buffer = $this->decode($buffer);
        $length = strlen($this->buffer);


        /**
         * rr 协议:
         * 只读取一次buffer,并立马响应,如果bufferSize=10,包长度为15,rr协议把该包当作两个包处理
         *
         */
        $this->readBuffer = $this->buffer;
        $this->readLength = $length;


        return false;
    }
}