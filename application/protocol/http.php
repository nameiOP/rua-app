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
    private $headEof = "\r\n\r\n";



    /**
     * header 读取结束
     * @var bool
     */
    private $_headEnd = false;


    /**
     * @var string 在headEof()中赋值,header文本信息,需要交给request对象处理
     */
    private $_requestHeader = '';


    /**
     * @var string post文本信息,
     */
    private $_requestBody = '';


    /**
     * @var
     */
    private $_request;


    /**
     * @var
     */
    private $_response;




    /**
     * 构造器
     */
    public function __construct(){

        parent::__construct();


        $this->bufferSize = 65535;
        $this->maxReadLength = 10485760;

        // http协议中,默认为短连接,需要由keep_alive开启长连接
        $this->setConnectLife(self::CONNECT_ONCE);


    }


    /**
     * 请求解析类
     * @return \app\protocol\http\request
     */
    private function _getRequest(){
        if(empty($this->_request)){
            $this->_request = \Builder::createObject('app\protocol\http\request');
        }
        return $this->_request;
    }




    /**
     * 响应解析类
     * @return \app\protocol\http\response
     */
    private function _getResponse(){
        if(empty($this->_response)){
            $this->_response = \Builder::createObject('app\protocol\http\response');
        }
        return $this->_response;
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
     * 是否读取结束
     * @param string $buffer
     * @return bool|null
     * true:    读取结束,消息完整;
     * false:   消息未完整;
     * null:    读取错误;
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
         */
        if(is_empty($this->buffer) ){
            $this->over();
            $this->setConnectLife(self::CONNECT_CLOSE);
            return false;
        }




        /**
         * 消息错误
         * 已接收的字节 >= 最大长度的话:
         * 就不用接收消息,防止内存泄漏,需要数据重置
         */
        if($this->readLength >= $this->maxReadLength){
            $this->over();
            return null;
        }



        // ===================================================


        /**
         * 检测header是否全部读取完整
         *
         * true:    header完整
         * false:   header不完整
         */

        //header数据不完整,继续接收消息
        if(false === $this->_headEof($this->buffer)){
            return false;
        }


        // header消息完整=====

        /**
         *
         * http1.1
         * 验证是否需要保持长连接
         *
         * 长连接:
         *      第一次 接收/发送数据 结束后,不关闭连接,继续接收数据,如果有数据,继续轮询;如果没有数据,则关闭客户端连接
         * 短连接:
         *      第一次 接收/发送数据 结束后,直接关闭连接,不会再次接收数据。
         *
         */
        if($this->_HeadKeepAlive()){
            $this->setConnectLife(self::CONNECT_KEEP);//长连接
        }else{
            $this->setConnectLife(self::CONNECT_ONCE);//短连接
        }

        //===================================================================

        /**
         * 检测 body 是否全部读取完整
         *
         * true:    header完整
         * false:   header不完整
         */

        //body数据不完整,继续接收消息
        if(false === $this->_bodyEOF($this->buffer)){
            return false;
        }





    }




    /**
     * 1)检测 http的header数据是否完整
     * 2)完整:初始化request对象
     *
     * @param string $buffer
     * @return bool
     * true:    header完整;
     * false:   header不完整;
     * @author liu.bin 2017/9/30 13:25
     */
    private function _headEof($buffer){

        // 检测head完整
        if($this->_headEnd){
            return true;
        }

        //readBuffer:读取buffer总数据
        $this->readBuffer .= $buffer;

        //readLength:读取buffer总长度
        $this->readLength = strlen($this->readBuffer);


        //检测是否有 headEof  "\r\n\r\n"
        if(strpos($this->readBuffer,  "\r\n\r\n")){
            list($this->_requestHeader,$this->_requestBody) = explode("\r\n\r\n",$this->readBuffer);
            //初始化request对象
            $this->_getRequest()->initBuffer($this->_requestHeader);
            $this->_headEnd = true;
            return true;
        }else{
            return false;
        }

    }





    /**
     * 1)检测 http 的 body 数据是否完整
     * 2)完整:初始化request对象
     *
     * @param string $buffer
     * @return bool
     *
     * true:    body 完整;
     * false:   body 不完整;
     *
     * @author liu.bin 2017/9/30 13:25
     */
    private function _bodyEOF($buffer){


        /**
         * 获取post过来的body数据
         * $contentLength == int body数据的长度
         * $contentLength == false 没有body数据
         */
        $contentLength = $this->_getRequest()->getHeaderValue('Content-Length');
        if(false === $contentLength){
            //没有body数据,整包,不用继续读取
            return true;
        }





        /**
         * 是否还有剩余数据没有接收
         * todo 获取已经读取的body长度
         * $leftLength: 还剩多少长度没有接收
         * 说明:每接收一次数据,$leftLength会变小一次
         */
        $leftLength = $contentLength - strlen($this->_requestBody);





        /**
         * 有剩余的body长度 && 剩余的body长度 小于等于 单次接收的$bufferSize
         *
         * 继续读取 buffer
         */
        if(($leftLength > 0) && ($leftLength <= $this->bufferSize)){
            //为了防止出现粘包之类的情况,重置下次接收buffer的长度,继续接收
            $this->bufferSize = $leftLength;
            return false;
        }





        /**
         * 粘包:下一个http的数据被读取过来
         * 处理:直接抛弃多余的数据
         * 说明:一般不会出现该情况,会根据剩余数据流动态修改 $bufferSize 的值,完整的读取数据包
         *
         * 不用读取buffer
         */
        if( $leftLength < 0 ){
            $this->_requestBody = substr($this->_requestBody,0,$contentLength);
            return true;
        }




        /**
         * 整包:刚好读取整包
         *
         * 不用读取buffer
         */
        if( 0 === $leftLength ){
            return true;
        }




        /**
         * 继续读取body
         */
        if($leftLength > 0){
            return false;
        }


        //默认
        return false;
    }




    /**
     * keep alive
     *
     * 如果不需要保持长连接:
     *      在客户端主动断开连接后,客户端socket处于可读状态,并且读的数据为空,则可以判断客户端socket 连接断开
     *
     * 如果需要保持长连接:
     *      在接收到空消息后,不会主动断开连接
     *
     *
     * @http1.1
     * @author liu.bin
     */
    private function _HeadKeepAlive(){

        $keep_alive = $this->_getRequest()->getHeaderValue('Connection');
        if( 'keep-alive' == $keep_alive){
            return true;
        }else{
            return false;
        }

    }



    /**
     * 重置
     */
    public function over(){
        $this->_headEnd = false;
        $this->_requestHeader = '';
        $this->_requestBody = '';
        parent::over();
        $this->bufferSize = 65535;
        $this->_getRequest()->initBuffer('');
    }




}