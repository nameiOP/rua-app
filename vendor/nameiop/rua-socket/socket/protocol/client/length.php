<?php
namespace rsk\protocol\client;


/**
 * Class length
 * @package rsk\protocol\server
 *
 * 一般用于长连接
 * 固定包头+变长包体
 */
class length extends clientProtocol
{


    /**
     * header 长度，采用固定4个字节的方式
     * @var int
     */
    protected $headSize = 4;




    /**
     * body 总长度，从header中解码获取，变长
     * @var int
     */
    protected $bodySize = 0;




    /**
     * buffer 包涵head头部
     * @var bool
     */
    protected $headInBuffer = true;




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
        $head = pack('N',strlen($mess));
		return $head . $mess;
	}




    /**
     * 是否继续读取buffer
     * 获取的body后，继续读取buffer,知道buffer读取完整，读取完整后，重置 buffer_size
     * @param string $buffer
     * @return bool false:不需要继续接收消息 ，true:继续接收消息
     * @author liu.bin 2017/9/29 14:37
     */
    public function readBuffer($buffer=''){

        //消息格式不正确
        if('' === $buffer || is_null($buffer)){
            $this->over();
            return false;
        }


        //如果输入的字节 >= 最大长度的话，数据错误，数据重置
        if($this->readLength >= $this->maxReadLength){
            $this->over();
            return false;
        }





        if($this->headInBuffer){


            /**
             * 读取有head的消息体
             */

            //解包
            $this->buffer = $this->decode($buffer);

            //获取头部
            $head = substr($buffer,0,$this->headSize);

            //获取body长度
            $this->bodySize = empty($head) ? 0 : unpack('N',$head)[1];

            //检测body是否为空
            if(0 === $this->bodySize){
                $this->over();
                return false;
            }

            //获取body
            $body = substr($buffer,$this->headSize);
            $this->headInBuffer = false;

        }else{


            /**
             * 读取没有head的消息体
             */
            $body = $buffer;
        }


        $this->readBuffer .= $body;
        $this->readLength += strlen($body);


        /**
         * 是否还有剩余数据没有接收：
         *
         * $leftLength 还剩多少长度没有接收
         */
        $leftLength = $this->bodySize - $this->readLength;


        /**
         * 没有剩余的body
         *
         * 不用接收buffer
         */
        if($leftLength <= 0){
            return false;
        }




        if($leftLength > $this->bufferSize){

            /**
             * 剩余的body长度 > 单次接受的bufferSize
             *
             * 继续接收buffer
             */
            return true;
        }else{


            /**
             * 剩余的body长度 <= 单次接收的bufferSize
             *
             * 重置下次接收buffer的长度,继续接收
             */
            $this->bufferSize = $leftLength;
            return true;
        }





    }





    /**
     * 读取结束
     * @return mixed
     * @author liu.bin 2017/9/30 9:57
     */
    public function over()
    {
        $this->headInBuffer = true;
        $this->bodySize = 0;
        $this->bufferSize = 10;
        parent::over();
    }




}