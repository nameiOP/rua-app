<?php


namespace rsk\traits;

trait streamsocketable
{




    /**
     * @var int 连接编号
     */
    protected $fd = 0;




    /**
     * php 套接字
     * @var
     */
    protected $socket;




    /**
     * @var bool socket 阻塞模式
     * true 阻塞模式
     * false 非阻塞模式
     */
    public $block = true;


    /**
     * 打印编号
     * @return string
     * @author liu.bin 2017/9/28 14:17
     */
    public function __toString()
    {
        return (string)$this->getFd();
    }






    /**
     * 创建socket套接字
     * @param $protocol
     * @param $ip
     * @param $port
     * @return bool
     * @throws \Exception
     * @author liu.bin 2017/10/26 15:10
     */
    public function createSocket($host,$port){

        //初始化socket配置
        $context_option = array();

        //backlog 是增加并发的关键
        if (!isset($context_option['socket']['backlog'])) {
            $context_option['socket']['backlog'] = 102400;
        }


        /**
         * 开启地址重复利用
         * http://php.net/manual/en/context.socket.php
         * http://blog.csdn.net/yaokai_assultmaster/article/details/68951150
         */
        if(!isset($context_option['socket']['so_reuseport'])){
            $context_option['socket']['so_reuseport'] = 1;
        }



        $_context = stream_context_create($context_option);

        //SIGPIPE;
        $param = 'tcp://'.$host.':'.$port;
        $socket = stream_socket_server($param, $errNo, $errStr,STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,$_context);


        if (!$socket) {
            throw new \Exception($errStr, $errNo);
        }

		$this->socket = $socket;
        $this->fd = socket_to_fd($this->socket);
        return true;
    }



	/**
	 * 接受socket请求
	 * 此方法可以创建客户端socket对象
     * @param resource $socket master-socket
     * @return resource|bool
	 */
	public function socketAccept($socket){
        //todo
        $peer_name = '';
		$msg_socket = stream_socket_accept($socket,0,$peer_name);
        return is_resource($msg_socket) ? $msg_socket : false;
	}



    /**
     * 发送消息
     */
    public function socketSend($socket,$data){
        return stream_socket_sendto($socket,$data);
    }


    /**
     * 从socket读取数据
     *
     * stream_socket_recvfrom 不支持阻塞读,所以需要自己判断
     *
     * @param $socket
     * @param $buffer_size
     * @param $flag
     * @return bool|string
     */
    public function socketReceive($socket,$buffer_size,$flag=MSG_DONTWAIT){

        if(MSG_DONTWAIT == $flag){
            //非阻塞读(默认)
            $buffer = stream_socket_recvfrom($socket,$buffer_size);
        }elseif(MSG_WAITALL == $flag){

            //阻塞读 stream_socket_recvfrom不支持阻塞读,需要用到while
            $left_buffer_len = $buffer_size;
            $buffer = '';
            while($left_buffer_len > 0){
                $buffer .= stream_socket_recvfrom($socket,$left_buffer_len);
                $read_buffer_len = strlen($buffer);
                $left_buffer_len = ($read_buffer_len < $left_buffer_len) ? ($left_buffer_len - $read_buffer_len) : 0;
            }
        }else{
            $buffer = stream_socket_recvfrom($socket,$buffer_size,$flag);
        }

        if($buffer){
            console("buffer is :".$buffer.' -- length: '.strlen($buffer),'stream_socket');
            return $buffer;
        }else{
            return false;
        }
    }




    /**
     * 从socket读取数据
     *
     * socket_read:
     *      1:PHP_NORMAL_READ   按最大长度读取,遇到 PHP_EOL 返回,应用程序需要自行判断消息完整;
     *      2:PHP_BINARY_READ   等价于 socket_recv;
     *
     *
     * @param $socket
     * @param $buffer_size
     * @param $read_type
     * @return bool|string
     *
     * @author liu.bin
     */
    public function socketRead($socket,$buffer_size,$read_type = PHP_BINARY_READ){


        if( PHP_BINARY_READ == $read_type ){
            //按最大长度读取,有值就立马返回
            $buffer = stream_socket_recvfrom($socket,$buffer_size,0);

        }elseif(PHP_NORMAL_READ == $read_type){
            //按最大长度和PHP_EOL读取,谁先满足就立即返回

            //不删除buffer读取
            $buffer = stream_socket_recvfrom($socket,$buffer_size,STREAM_PEEK);
            //已经读满$buffer_size && 读取的数据包涵 PHP_EOL
            if( $buffer && ( false !== ($str_pos = strpos($buffer,PHP_EOL)) ) ){
                //删除buffer读取
                $buffer = stream_socket_recvfrom($socket,($str_pos + strlen(PHP_EOL)));
            }else{
                $buffer = stream_socket_recvfrom($socket,$buffer_size);
            }

        }else{
            //默认 按$read_type模式读取(STREAM_PEEK,STREAM_OOB)
            $buffer = stream_socket_recvfrom($socket,$buffer_size,$read_type);
        }

        if($buffer){
            return $buffer;
        }else{
            return false;
        }
    }


    /**
     * socket_select IO复用模型
     * @param array $read
     * @param array $write
     * @param array $except
     * @param $tv_sec
     * @param null $tv_usec
     * @return int
     *
     * @author liu.bin
     */
    public function socketSelect(array &$read, array &$write, array &$except, $tv_sec, $tv_usec = null){
        return stream_select($read, $write, $except, $tv_sec, $tv_usec);
    }




    /**
     * 设置socket为非阻塞模式
     * stream家族函数,都是非阻塞
     * @param $socket
     * @return bool
     */
    public function socketSetNonBlock($socket){

        if(stream_set_blocking($socket,0)){
            $this->block = false;
            return true;
        }
        return false;
    }



    /**
     * 设置socket为阻塞模式
     * stream家族函数,都是非阻塞
     * @param $socket
     * @return bool
     */
    public function socketSetBlock($socket){
        if(stream_set_blocking($socket,1)){
            $this->block = true;
            return true;
        }
        return false;

    }


    /**
     * 关闭客户端连接
     * @param $socket
     * @return bool
     */
    public function socketClose($socket,$how=STREAM_SHUT_WR){
        stream_socket_shutdown($socket,$how);
        return fclose($socket);
    }



    /**
     * 获取socket
     * @return mixed
     * @author liu.bin 2017/9/28 15:06
     */
    public function getSocket(){
        return $this->socket;
    }





    /**
     * 获取连接编号
     * @author liu.bin 2017/9/28 14:18
     */
    public function getFd(){
        return $this->fd;
    }

}
