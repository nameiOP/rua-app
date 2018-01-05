<?php
namespace rsk\protocol\client;


/**
 * EOF检测协议:做短连接用
 *
 * 在数据发送结尾加入特殊字符，表示一个请求传输完毕
 * 该协议只解决数据包合并，不解决数据包拆分。
 *
 * 注意:
 * 一个数据包中包涵多个/r/n 或者 多个包被tcp客户端打包成一个包发送的时候:
 * 数据包会出现该情况:11/r/n22223333/r/n
 * bufferSize = 10,
 * 第一条消息:11
 * 第一条消息:3333
 * 2222数据丢失,因为在第一次读包的数据为11/r/n2222,第二次读包数据为[3333/r/n]
 * 此模式适合一次只发一个包,响应后即断开。比如http的get模式
 *
 * Class text
 * @package protocol\server
 */
class eof extends clientProtocol
{
	

	//eof边界检测符
	private $package_eof = '/r/n';
	
	//边界符正则
    private $eof_pattern = '/\/r\/n/';



	/**
     * 数据解包
     * @param $mess string
     * @return string
     * */
    public function decode($mess){
        $mess = str_replace(PHP_EOL, '', $mess);
        return $mess;
	}



    /**
     * 数据打包
     * @param $mess string
     * @return string
     * */
    public function encode($mess){
        $mess = str_replace(PHP_EOL, '', $mess);
		return $mess . $this->package_eof;
	}





    /**
     * 是否继续读取buffer
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


        //如果接收的字节 >= 最大长度的话，就不用接收消息,数据重置
        if($this->readLength >= $this->maxReadLength){
            $this->over();
            return false;
        }


        //解码
        $this->buffer = $this->decode($buffer);
        return $this->eof($this->buffer) ? false : true;

    }



    /**
     * 检测 数据到达边界
     * @param string $buffer
     * @return bool true:到达边界；false没有到达边界
     * @author liu.bin 2017/9/30 13:25
     */
    private function eof($buffer){

        $this->readBuffer .= $buffer;

        //检测是否有 package_eof
        if(preg_match($this->eof_pattern, $this->readBuffer)){

            list($this->readBuffer) = explode($this->package_eof,$this->readBuffer,2);
            $this->readLength = strlen($this->readBuffer);
            return true;

        }else{

            $this->readLength = strlen($this->readBuffer);
            return false;

        }

    }

}