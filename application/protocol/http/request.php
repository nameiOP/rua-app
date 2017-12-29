<?php

namespace app\protocol\http;


/**
 * http 请求解析类
 * Class request
 */
class request{


    /**
     * 分割符
     * @var string
     */
    private $eof = '\r\n\r\n';


    /**
     * 请求消息数据
     * @var string
     */
    private $_requestData = '';


    /**
     * 请求消息头
     * @var string
     */
    private $_requestHeader = '';


    /**
     * 请求消息body
     * @var string
     */
    private $_requestBody = '';


    /**
     * http请求方式
     * @var string
     */
    private $_requestMethod = '';




    /**
     * http请求方式
     * @var array
     */
    private $methods = ['GET','POST','PUT','DELETE','HEAD','OPTIONS'];




    /**
     * @param $buffer
     */
    public function __construct(){

    }


    /**
     * 设置buffer
     * @param $buffer
     */
    public function initBuffer($buffer){
        $this->_requestData = $buffer;
        $this->_requestBody = '';
        $this->_requestHeader = '';
        $this->_requestMethod = '';
        return $this;
    }


    /**
     * 获取请求header
     */
    public function getHeader(){

        if('' === $this->_requestHeader){
            list($this->_requestHeader) = explode($this->eof,$this->_requestData,2);
        }
        return $this->_requestHeader;
    }


    /**
     * 获取请求类型
     * @return string
     */
    public function getMethod(){

        if('' === $this->_requestMethod){
            $method = substr($this->getHeader(),0,strpos($this->getHeader(),' '));
            $this->_requestMethod = in_array($method,$this->methods) ? $method : '';
        }
        return $this->_requestMethod;
    }


    /**
     * 获取header中值
     * @param $key
     * @return bool
     */
    public function getHeaderValue($key=''){

        /**
         * \S 匹配任何非空白字符
         * header数据中,一般用换行符\r\n(空字符)来分割,所以此处只会匹配一行
         */
        $patton = "/\r\n".$key.": ?(\S+)/i";
        if( preg_match($patton,$this->getHeader(),$match) ){
            $value = isset($match[1]) ? (int)$match[1] : false;
            return $value;
        }else{
            return false;
        }
    }





    /**
     * 获取请求数据长度
     */
    public function getRequestSize(){

        if( 'GET' === $this->getMethod() || 'OPTIONS' === $this->getMethod() || 'HEAD' === $this->getMethod()){
            return strlen($this->getHeader()) + 4;
        }

        console($this->getHeader(),'header');

        $match = array();
        $patton = "/\r\nContent-Length: ?(\d+)/i";
        if( preg_match($patton,$this->getHeader(),$match) ){
            $length = isset($match[1]) ? $match[1] : 0;
            return $length + strlen($this->getHeader()) + 4;
        }
        return 0;
    }




    /**
     * 获取请求body
     * @return string
     */
    public function getBody(){

        if('' === $this->_requestBody){
            list($this->_requestHeader,$this->_requestBody) = explode($this->eof,$this->_requestData,2);
        }
        return $this->_requestBody;
    }





    /**
     * 输入
     */
    private function in(){


        /**
         * 全局变量
         */
        $_POST = $_GET = $_COOKIE = $_REQUEST = $_SESSION = $_FILES = [];


        /**
         * HTTP_RAW_POST_DATA
         */
        $GLOBALS['HTTP_RAW_POST_DATA'] = '';

        // $_SERVER TODO
        $_SERVER = [
            "QUERY_STRING"      => '',
            "REQUEST_METHOD"    => '',
            "REQUEST_URI"       => '',
            "SERVER_PROTOCOL"   => '',

        ];
        $header = explode("\r\n",$this->getHeader());
        list($_SERVER["REQUEST_METHOD"],$_SERVER["REQUEST_URI"],$_SERVER['SERVER_PROTOCOL']) = explode(' ',$header[0]);


        $http_post_boundary = '';
        unset($header[0]);
        foreach($header as $content){
            if(empty($content)){
                continue;
            }

            list($key,$value)   = explode(":",$content,2);
            $key                = str_replace('-','_',strtoupper($key));
            $value              = trim($value);
            $_SERVER["HTTP_".$key] = $value;
            switch($key){

                // HTTP_HOST
                case 'HOST':
                    $tem = explode(':',$value);
                    $_SERVER['SERVER_NAME'] = $tem[0];
                    if(isset($tem[1])){
                        $_SERVER['_SERVER_POST'] = $tem[1];
                    }
                    break;

                //cookie
                case 'COOKIE':
                    parse_str(str_replace('; ','&',$_SERVER['HTTP_COOKIE']),$_COOKIE);
                    break;

                // content-type
                case 'CONTENT_TYPE':

                    $p = '/boundary="(\S+)"?/';
                    if( !preg_match($p,$value,$match)){

                        if($pos = strpos($value,';')){
                            $_SERVER['CONTENT_TYPE'] = substr($value,0,$pos);
                        }else{
                            $_SERVER['CONTENT_TYPE'] = $value;
                        }

                    }else{
                        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
                        $http_post_boundary      = '--' . $match[1];
                    }

                    break;
                case 'CONTENT_LENGTH':
                    $_SERVER['CONTENT_LENGTH'] = $value;
                    break;

            }


        }



        // $_POST 处理post数据
        if('POST' === $_SERVER['REQUEST_METHOD'] && isset($_SERVER['CONTENT_TYPE']) ){

            switch($_SERVER['CONTENT_TYPE']){
                case 'multipart/form-data':
                    // todo 处理文件上传
                    $this->requestFileIn($this->_requestBody,$http_post_boundary);
                    break;
                case 'application/x-www-form-urlencoded':
                    parse_str($this->_requestBody,$_POST);
                    break;
            }

        }


        // http_raw_request_data http_raw_post_data
        $GLOBALS['HTTP_RAW_REQUEST_DATA'] = $GLOBALS['HTTP_RAW_POST_DATA'] = $this->_requestBody;


        // $_GET query_string
        $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY);
        if($_SERVER['QUERY_STRING']){
            parse_str($_SERVER['QUERY_STRING'],$_GET);
        }else{
            $_SERVER['QUERY_STRING'] = '';
        }


        // REQUEST
        $_REQUEST = array_merge($_GET,$_POST);

        // TODO REMOTE_ADDR REMOTE_PORT
        $_SERVER['REMOTE_ADDR'] = '';
        $_SERVER['REMOTE_PORT'] = '';

    }


    /**
     * todo
     * 处理文件上传
     */
    public function requestFileIn($body,$http_post_boundary){



    }




}