<?php
namespace app\protocol;
use rsk\protocol\server\serverProtocol;


/**
 * Class roo
 * @package rsk\protocol\server
 *
 */
class http extends serverProtocol
{


    //eof边界检测符
    private $headEof = '\r\n\r\n';



    /**
     * header 读取结束
     * @var bool
     */
    private $headEnd = false;


    /**
     * @var string
     */
    private $_requestHeader = '';

    /**
     * @var string
     */
    private $_requestBody = '';

    /**
     * @var
     */
    private $request;


    /**
     * @var
     */
    private $response;




    /**
     * 构造器
     */
    public function __construct(){

        $this->bufferSize = 65535;
        $this->maxReadLength = 10485760;
        parent::__construct();
    }


    /**
     * 请求解析类
     * @return \app\protocol\http\request
     */
    private function getRequest(){
        if(empty($this->request)){
            $this->request = \Builder::createObject('app\protocol\http\request');
        }
        return $this->request;
    }




    /**
     * 响应解析类
     * @return \app\protocol\http\response
     */
    private function getResponse(){
        if(empty($this->response)){
            $this->response = \Builder::createObject('app\protocol\http\response');
        }
        return $this->response;
    }




	/**
     * 数据解包
     * @param $buffer string
     * @return string
     * */
    public function decode($buffer){
        return $buffer;
	}



    /**
     * 数据打包
     * @param $buffer string
     * @return string
     * */
    public function encode($buffer){

        return $buffer;
	}




    /**
     * 是否通知connect继续读取消息
     *
     * @param string $buffer
     * @return bool false:不需要继续接收消息 ，true:继续接收消息
     * @author liu.bin 2017/9/29 14:37
     */
    public function readBuffer($buffer = '')
    {

        //console(1,'http');

        //消息格式不正确
        if(is_empty($buffer)){
            $this->over();
            return false;
        }


        //console(2);

        /**
         * 已接收的字节 >= 最大长度的话:
         * 就不用接收消息,防止内存泄漏,需要数据重置
         */
        if($this->readLength >= $this->maxReadLength){
            $this->over();
            return false;
        }

        //console(3,'http');

        /**
         * buffer解码
         */
        $this->buffer = $this->decode($buffer);

        console($this->buffer,'http - buffer');

        //检测header是否全部获取完整
        if(!$this->headEof($this->buffer)){
            //head没有接收完整,继续接收消息
            //console('http receive over, buffer: '.$buffer,'http');
            return true;
        }

        //console(4,'http');

        /**
         * header 全部接收完整
         * 检测 header,body,content-length,通过content-length获取 requestSize
         */
        $request = $this->getRequest()->initBuffer($this->readBuffer);
        $contentLength = $request->getHeaderValue('Content-length');
        if(false === $contentLength){
            //console(5,'http');
            return false;
        }


        //console(6,'http');

        /**
         * 是否还有剩余数据没有接收：
         *
         * $leftLength 还剩多少长度没有接收
         */
        $leftLength = $contentLength - strlen($this->_requestBody);



        /**
         * 整包:不用接收buffer
         */
        if( 0 === $leftLength){
            return false;
        }

        /**
         * 粘包:直接抛弃多余的数据
         * 不用接收buffer
         */
        if(0 > $leftLength){
            $this->_requestBody = substr($this->_requestBody,0,$contentLength);
            return false;
        }


        //console(7);

        //$leftLength > 0
        if($leftLength > $this->bufferSize){

            /**
             * 剩余的body长度 > 单次接受的bufferSize
             *
             * 继续接收buffer
             */
            //console(8);
            return true;
        }else{


            /**
             * 剩余的body长度 <= 单次接收的bufferSize
             *
             * 重置下次接收buffer的长度,继续接收
             * 所以一般不会出现粘包之类的情况
             */
            $this->bufferSize = $leftLength;
            //console(9);
            return true;
        }


    }




    /**
     * 检测 数据到达边界
     * @param string $buffer
     * @return bool true:到达边界；false没有到达边界
     * @author liu.bin 2017/9/30 13:25
     */
    private function headEof($buffer){

        // 检测head完整
        if($this->headEnd){
            return true;
        }

        $this->readBuffer .= $buffer;
        $this->readLength = strlen($this->readBuffer);

        //检测是否有 headEof  "\r\n\r\n"
        if(strpos($this->readBuffer,  "\r\n\r\n")){
            list($this->_requestHeader,$this->_requestBody) = explode("\r\n\r\n",$this->readBuffer);
            $this->headEnd = true;
            return true;
        }else{
            return false;
        }

    }


    /**
     * 处理http请求
     * @param string $buffer
     */
    private function http(){

        $request = $this->getRequest()->initBuffer($this->readBuffer);

        $method = $request->getMethod();
        console($method);

        $requestSize = $request->getRequestSize();
        console($requestSize,'reSize');

    }


    /**
     * 重置
     */
    public function over(){
        $this->headEnd = false;
        $this->_requestHeader = '';
        $this->_requestBody = '';
        parent::over();
        $this->bufferSize = 65535;
    }




}