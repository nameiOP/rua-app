<?php
namespace rsk\protocol\server;


/**
 *
 *
 * EOF协议:
 *      数据封包:   USER_DATA.PHP_EOL
 *      读取方式:   socket_read(PHP_NORMAL_READ)
 *      说明:      循环读取 $buffer_size 长度的数据,遇到 PHP_EOL 数据包读取完成;
 *
 *
 *
 * Class eof
 * @package protocol\server
 */
class eof extends serverProtocol
{
	

	//eof边界检测符
	private $package_eof = PHP_EOL;




    /**
     * 构造器
     */
    public function __construct(){

        parent::__construct();


        /**
         * tcp 协议中,如果做短连接,需要设置为 CONNECT_ONCE;
         * 也可以根据协议内容,动态的设置TCP为长连接还是短连接
         */
        //$this->setConnectLife(self::CONNECT_ONCE);

    }

    

    public function init($fd){
        $this->fd = $fd;
    }




	/**
     * 数据解包
     * @param $mess string
     * @return string
     * */
    public function decode($mess){
        return $mess;
	}



    /**
     * 数据打包
     * @param $mess string
     * @return string
     * */
    public function encode($mess){
		return $mess;
	}





    /**
     * 是否读取结束
     * @param string $buffer
     * @return bool|null
     * true:读取结束,消息完整;
     * false:消息未完整;
     * null:读取错误;
     * @author liu.bin 2017/9/29 14:37
     */
    public function readEOF($buffer = '')
    {


        $this->buffer = $buffer;


        //连接关闭
        if(self::CONNECT_CLOSE == $this->getConnectLife()){
            return null;
        }



        /**
         * 触发socket_select()读 如果长度内容为空,则可以判定是客户端发送FIN标识数据包,主动请求关闭;
         *
         * 在IO-loop模型中:
         *      第一次读取消息为空,设置关闭连接,返回false,
         *      connect发现是false,再次读取消息,发现是关闭连接,返回null,
         *      connect发现是null,则关闭connect连接
         *
         */
        if(is_empty($this->buffer)){
            $this->bufferRecovery();
            $this->setConnectLife(self::CONNECT_CLOSE);
            return false;
        }




        //如果接收的字节 >= 最大长度的话，就不用接收消息,数据重置
        if($this->readLength >= $this->maxReadLength){
            $this->bufferRecovery();
            $this->setConnectLife(self::CONNECT_CLOSE);
            return null;
        }

        if($this->_eof($this->buffer)){
            //到达EOF,不需要继续读取
            $this->_readBuffer = $this->readBuffer;
            return true;
        }else{
            //没有到家边界,需要继续读取
            return false;
        }

    }



    /**
     * 检测 数据到达边界
     * @param string $buffer
     * @return bool
     * true:    到达EOF;
     * false:   没有到达EOF;
     * @author liu.bin 2017/9/30 13:25
     */
    private function _eof($buffer){

        $this->readBuffer .= $buffer;

        //检测是否有 eof
        if(false === ($str_pos = strpos($this->readBuffer,$this->package_eof)) ){

            //没有eof
            $this->readLength = strlen($this->readBuffer);
            return false;

        }else{

            //有eof
            $this->readBuffer = substr($this->readBuffer,0,$str_pos);
            $this->readLength = strlen($this->readBuffer);
            return true;

        }

    }

}